<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Section;
use App\Services\UConnApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrawlUConnCourses extends Command
{
    protected $signature = 'uconn:crawl
                            {--subject= : Specific subject to crawl (e.g., CSE, MATH)}
                            {--campus=STORR@STORRS : Campus to crawl}
                            {--fresh : Clear existing data before crawling}
                            {--details : Fetch detailed information for each section}';

    protected $description = 'Crawl UConn course data and store in database';

    private $apiService;
    private $stats = [
        'courses_created' => 0,
        'courses_updated' => 0,
        'sections_created' => 0,
        'sections_updated' => 0,
        'errors' => 0,
    ];

    public function handle(UConnApiService $apiService)
    {
        $this->apiService = $apiService;
        
        $this->info('Starting UConn Course Crawler');
        $this->info('');

        // Fresh start if requested
        if ($this->option('fresh')) {
            $this->warn('Clearing existing data');
            DB::table('sections')->truncate();
            DB::table('courses')->truncate();
            $this->info('Database cleared');
            $this->info('');
        }

        $subject = $this->option('subject');
        $campus = $this->option('campus');

        if ($subject) {
            $this->crawlSubject($subject, $campus);
        } else {
            $this->crawlAllSubjects($campus);
        }
    }

    private function crawlAllSubjects($campus)
    {
        $subjects = $this->apiService->getSubjects();
        $subjectCount = count($subjects);
        
        $this->info("Crawling {$subjectCount} subjects from {$campus}...");
        $this->info('');

        $bar = $this->output->createProgressBar($subjectCount);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        foreach ($subjects as $code => $name) {
            if (empty($code)) continue; // Skip "All Subjects"
            
            $bar->setMessage("Processing {$code}");
            $this->crawlSubject($code, $campus, false);
            $bar->advance();
        }

        $bar->finish();
        $this->info('');
        $this->info('');
    }

    private function crawlSubject($subjectCode, $campus, $showProgress = true)
    {
        if ($showProgress) {
            $this->info("Crawling {$subjectCode} courses");
        }

        // Search for all courses in this subject
        $results = $this->apiService->searchClasses($campus, $subjectCode);

        if (isset($results['error'])) {
            $this->error("Error fetching {$subjectCode}: {$results['error']}");
            $this->stats['errors']++;
            return;
        }

        if (empty($results) || !is_array($results)) {
            if ($showProgress) {
                $this->warn("No courses found for {$subjectCode}");
            }
            return;
        }

        $courseCount = count($results);
        if ($showProgress) {
            $this->info("Found {$courseCount} sections");
        }

        // Group sections by course code
        $courseGroups = [];
        foreach ($results as $section) {
            $code = $section['code'];
            if (!isset($courseGroups[$code])) {
                $courseGroups[$code] = [];
            }
            $courseGroups[$code][] = $section;
        }

        // Process each course
        foreach ($courseGroups as $courseCode => $sections) {
            $this->processCourse($courseCode, $sections);
        }
    }

    private function processCourse($courseCode, $sections)
    {
        // Use the first section to get course-level info
        $firstSection = $sections[0];
        
        // Extract subject and catalog number from code (
        $parts = explode(' ', $courseCode);
        $subject = $parts[0];
        $catalogNumber = $parts[1] ?? '';

        // Create or update course
        $course = Course::updateOrCreate(
            ['code' => $courseCode],
            [
                'subject' => $subject,
                'catalog_number' => $catalogNumber,
                'title' => $firstSection['title'],
                'term' => '1263', // Spring 2026
            ]
        );

        if ($course->wasRecentlyCreated) {
            $this->stats['courses_created']++;
        } else {
            $this->stats['courses_updated']++;
        }

        // Get detailed info if requested (only need one per course for description)
        if ($this->option('details') && !$course->description) {
            $details = $this->apiService->getCourseDetails($firstSection);
            if (!isset($details['fatal']) && isset($details['description'])) {
                $course->update([
                    'description' => $details['description'] ?? null,
                    'campus' => $this->extractCampus($details['camp_html'] ?? ''),
                    'credits' => $this->extractCredits($details['hours_html'] ?? ''),
                    'prerequisites' => $details['registration_restrictions'] ?? null,
                ]);
            }
        }

        // Process all sections
        foreach ($sections as $sectionData) {
            $this->processSection($course, $sectionData);
        }
    }

    private function processSection(Course $course, array $data)
    {
        // Parse meeting times
        $meetingTimes = null;
        if (!empty($data['meetingTimes'])) {
            $meetingTimes = is_string($data['meetingTimes']) 
                ? json_decode($data['meetingTimes'], true) 
                : $data['meetingTimes'];
        }

        // Extract basic section info
        $sectionData = [
            'course_id' => $course->id,
            'key' => $data['key'],
            'mpkey' => $data['mpkey'] ?? null,
            'section_number' => $data['no'],
            'type' => $data['schd'],
            'linked_crns' => $data['linked_crns'] ?? null,
            'is_enrollment_section' => ($data['is_enroll_section'] ?? '0') === '1',
            'instruction_mode' => $data['instmode'] ?? null,
        ];

        // Get detailed seat info if --details flag is used
        if ($this->option('details')) {
            $details = $this->apiService->getCourseDetails($data);
            if (!isset($details['fatal'])) {
                $seatInfo = $this->apiService->parseSeatInfo($details);
                if ($seatInfo) {
                    $sectionData['max_enrollment'] = $seatInfo['max_enrollment'];
                    $sectionData['seats_available'] = $seatInfo['available'];
                }
                
                if (isset($details['instmode'])) {
                    $sectionData['instruction_mode'] = $details['instmode'];
                }
            }
        }

        // Create or update section
        $section = Section::updateOrCreate(
            ['crn' => $data['crn']],
            $sectionData
        );

        if ($section->wasRecentlyCreated) {
            $this->stats['sections_created']++;
        } else {
            $this->stats['sections_updated']++;
        }
    }

    private function extractCampus($campusHtml)
    {
        return strip_tags($campusHtml);
    }

    private function extractCredits($hoursHtml)
    {
        // Extract "3" from "3 Units"
        if (preg_match('/(\d+)\s*Units?/i', $hoursHtml, $matches)) {
            return $matches[1];
        }
        return null;
    }

}

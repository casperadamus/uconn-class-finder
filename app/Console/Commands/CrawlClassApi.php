<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UConnApiService;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Facades\Log;

class CrawlClassApi extends Command
{
    protected $signature = 'app:crawl-classes';
    protected $description = 'Crawl the UConn class API and save data to the database using UConnApiService';

    public function handle(UConnApiService $apiService)
    {
        $this->info('Starting class data crawl using UConnApiService...');
        
        $subjects = $apiService->getSubjects();
        $totalSubjects = count($subjects) - 1; 
        $this->info("Found {$totalSubjects} subjects to crawl.");

        $progressBar = $this->output->createProgressBar($totalSubjects);
        $progressBar->start();

        foreach ($subjects as $subjectAbbr => $subjectName) {
            if (empty($subjectAbbr)) {
                continue;
            }

            $progressBar->setMessage("Processing: $subjectAbbr");
            $results = $apiService->searchClasses('STORR@STORRS', $subjectAbbr);

            if (isset($results['error'])) {
                $this->error("\nFailed to fetch data for subject: $subjectAbbr. Error: " . $results['error']);
                Log::error("Crawl Error ($subjectAbbr): " . $results['error'], $results['api_response'] ?? []);
                continue;
            }

            if (empty($results)) {
                // This is a warning, not an error
                Log::info("No classes found for subject: $subjectAbbr");
                continue;
            }

            // 5. Process and save the results
            foreach ($results as $classData) {
                
                //dd($classData)

                try {
                    // 1. Split the "code" field (e.g., "AAAS 1000")
                    // We use list() to assign the two parts to variables
                    // We add ' ' and 2 to handle any missing data gracefully
                    list($subject, $catalog_number) = explode(' ', $classData['code'] . ' ', 2);
                    
                    // Trim whitespace
                    $subject = trim($subject);
                    $catalog_number = trim($catalog_number);

                    // Skip if data is bad
                    if (empty($subject) || empty($catalog_number)) {
                        Log::warning('Skipping class with invalid code: ' . $classData['code']);
                        continue;
                    }

                    // 5a. Save the Course
                    $course = Course::updateOrCreate(
                        [
                            'subject' => $subject, // <-- Use new variable
                            'catalog_number' => $catalog_number // <-- Use new variable
                        ],
                        [
                            'title' => $classData['title'] ?? 'N/A' // <-- Use correct key 'title'
                        ]
                    );

                    // 5b. Save the Section
                    Section::updateOrCreate(
                        [
                            'class_number' => $classData['crn'] // <-- Use correct key 'crn'
                        ],
                        [
                            'course_id' => $course->id,
                            'professor' => $classData['instr'] ?? 'N/A', // <-- Use correct key 'instr'
                            'schedule' => $classData['meets'] ?? 'N/A', // <-- Use correct key 'meets'
                            'open_seats' => 0 // <-- Set to 0, as this data isn't in this response
                        ]
                    );

                } catch (\Exception $e) {
                    // 2. Update the error line to use the correct key
                    $this->error("\nFailed to save data for class: " . $classData['crn'] ?? 'Unknown');
                    $this->error("Error: " . $e->getMessage());
                    Log::error("Database Save Error: " . $e->getMessage(), $classData);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nSuccessfully crawled and saved all class data!");
        return 0; // Success
    }
}
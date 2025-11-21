<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Course;
use App\Models\Section;

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                   UCONN CLASS FINDER - SYSTEM DEMONSTRATION                ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// 1. DATABASE STATISTICS
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 1. DATABASE STATISTICS                                                  │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

$totalCourses = Course::count();
$totalSections = Section::count();
$availableSections = Section::where('status', 'A')->count();
$fullSections = Section::where('status', 'F')->count();

echo "   Total Courses Stored:         {$totalCourses}\n";
echo "   Total Sections Stored:        {$totalSections}\n";
echo "   Available Sections:           {$availableSections}\n";
echo "   Full Sections:                {$fullSections}\n";
echo "   Average Sections per Course:  " . round($totalSections / max($totalCourses, 1), 2) . "\n\n";

// Section types breakdown
echo "   Section Types Distribution:\n";
$types = Section::select('type', \DB::raw('count(*) as count'))
    ->groupBy('type')
    ->orderBy('count', 'desc')
    ->get();

foreach ($types as $type) {
    $label = str_pad($type->type, 5);
    $bar = str_repeat('█', min(50, $type->count / 10));
    echo "   {$label} {$bar} {$type->count}\n";
}

echo "\n";

// ============================================================================
// 2. DEMONSTRATE RELATIONSHIPS
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 2. PARENT-CHILD RELATIONSHIPS (Course → Sections)                       │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

$sampleCourse = Course::with('sections')->has('sections', '>=', 3)->first();

if ($sampleCourse) {
    echo "   PARENT RECORD (Course):\n";
    echo "   ─────────────────────────\n";
    echo "   ID:           {$sampleCourse->id}\n";
    echo "   Code:         {$sampleCourse->code}\n";
    echo "   Title:        {$sampleCourse->title}\n";
    echo "   Campus:       {$sampleCourse->campus}\n";
    echo "   Credits:      {$sampleCourse->credits}\n\n";
    
    echo "   CHILD RECORDS (Sections) - Linked via course_id = {$sampleCourse->id}:\n";
    echo "   ───────────────────────────────────────────────────────────────────\n";
    
    foreach ($sampleCourse->sections->take(5) as $section) {
        $seats = $section->max_enrollment > 0 
            ? "{$section->seats_available}/{$section->max_enrollment}" 
            : "N/A";
        $status = $section->status == 'A' ? '✓ OPEN' : '✗ FULL';
        
        echo "   • CRN {$section->crn} | Type: {$section->type} | Section: {$section->section_number}\n";
        echo "     course_id: {$section->course_id} (links to parent) | Seats: {$seats} | {$status}\n";
        echo "     Time: " . ($section->meeting_time_display ?? 'TBA') . "\n\n";
    }
    
    if ($sampleCourse->sections->count() > 5) {
        echo "   ... and " . ($sampleCourse->sections->count() - 5) . " more sections\n\n";
    }
}

// ============================================================================
// 3. DEMONSTRATE SEAT TRACKING
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 3. REAL-TIME SEAT AVAILABILITY TRACKING                                 │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

$sectionsWithSeats = Section::with('course')
    ->where('max_enrollment', '>', 0)
    ->orderByRaw('seats_available / max_enrollment ASC')
    ->limit(10)
    ->get();

echo "   Top 10 Sections by Seat Availability (Closest to Full):\n";
echo "   ─────────────────────────────────────────────────────────────────────\n\n";

foreach ($sectionsWithSeats as $section) {
    $percentage = round(($section->seats_available / $section->max_enrollment) * 100);
    $barLength = max(0, min(50, (int)(50 * $section->seats_available / $section->max_enrollment)));
    $bar = str_repeat('█', $barLength);
    $empty = str_repeat('░', max(0, 50 - $barLength));
    $status = $percentage == 0 ? '[FULL]' : '[OPEN]';
    
    echo "   {$section->course->code} - CRN {$section->crn} {$status}\n";
    echo "   {$bar}{$empty} {$section->seats_available}/{$section->max_enrollment} ({$percentage}%)\n";
    echo "   Type: {$section->type} | Time: " . ($section->meeting_time_display ?? 'TBA') . "\n\n";
}

// ============================================================================
// 4. DEMONSTRATE DATA FRESHNESS
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 4. DATA FRESHNESS & UPDATES                                             │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

$recentCourses = Course::orderBy('updated_at', 'desc')->limit(5)->get();

echo "   Most Recently Updated Courses:\n";
echo "   ─────────────────────────────────────────────────────────────────────\n\n";

foreach ($recentCourses as $course) {
    $timeAgo = $course->updated_at->diffForHumans();
    echo "   • {$course->code} - {$course->title}\n";
    echo "     Last Updated: {$course->updated_at->format('M d, Y H:i:s')} ({$timeAgo})\n\n";
}

// ============================================================================
// 5. DEMONSTRATE SEARCH CAPABILITIES
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 5. SEARCH & FILTER CAPABILITIES                                        │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

// Search by subject
$subjects = ['CSE', 'ANTH', 'MATH', 'BIOL'];
echo "   Courses by Subject:\n";
echo "   ─────────────────────────────────────────────────────────────────────\n";

foreach ($subjects as $subject) {
    $count = Course::where('subject', $subject)->count();
    $sectionCount = Section::whereHas('course', function($q) use ($subject) {
        $q->where('subject', $subject);
    })->count();
    
    if ($count > 0) {
        echo "   {$subject}: {$count} courses, {$sectionCount} sections\n";
    }
}

echo "\n";

// Available seats summary
echo "   Seat Availability Summary:\n";
echo "   ─────────────────────────────────────────────────────────────────────\n";

$totalSeats = Section::sum('max_enrollment');
$availableSeats = Section::sum('seats_available');
$occupiedSeats = $totalSeats - $availableSeats;
$occupancyRate = $totalSeats > 0 ? round(($occupiedSeats / $totalSeats) * 100, 1) : 0;

echo "   Total Seats:      {$totalSeats}\n";
echo "   Occupied Seats:   {$occupiedSeats}\n";
echo "   Available Seats:  {$availableSeats}\n";
echo "   Occupancy Rate:   {$occupancyRate}%\n\n";

// ============================================================================
// 6. DEMONSTRATE LINKED SECTIONS
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 6. LINKED SECTIONS (Lectures ↔ Labs ↔ Discussions)                     │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

$linkedSection = Section::with('course')
    ->whereNotNull('linked_crns')
    ->where('linked_crns', '!=', '')
    ->first();

if ($linkedSection) {
    echo "   Course: {$linkedSection->course->code} - {$linkedSection->course->title}\n\n";
    echo "   PRIMARY SECTION:\n";
    echo "   CRN {$linkedSection->crn} - {$linkedSection->type} {$linkedSection->section_number}\n";
    echo "   Time: " . ($linkedSection->meeting_time_display ?? 'TBA') . "\n\n";
    
    echo "   LINKED SECTIONS:\n";
    $linkedCrns = explode(',', $linkedSection->linked_crns);
    foreach ($linkedCrns as $crn) {
        $linked = Section::where('crn', trim($crn))->first();
        if ($linked) {
            $seats = $linked->max_enrollment > 0 
                ? "({$linked->seats_available}/{$linked->max_enrollment})" 
                : "";
            echo "   CRN {$linked->crn} - {$linked->type} {$linked->section_number} {$seats}\n";
            echo "      Time: " . ($linked->meeting_time_display ?? 'TBA') . "\n";
        }
    }
    echo "\n";
}

// ============================================================================
// 7. SYSTEM CAPABILITIES
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 7. SYSTEM CAPABILITIES                                                  │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

echo "   ✓ Automated data crawling from UConn API\n";
echo "   ✓ Real-time seat availability tracking\n";
echo "   ✓ Parent-child course-section relationships\n";
echo "   ✓ Multiple section types: LEC, LAB, DIS, SEM, LSA, etc.\n";
echo "   ✓ Meeting time parsing and display\n";
echo "   ✓ Linked section tracking (lectures with labs/discussions)\n";
echo "   ✓ Course prerequisites and descriptions\n";
echo "   ✓ Web interface for browsing (http://127.0.0.1:8000/courses)\n";
echo "   ✓ RESTful API endpoints for programmatic access\n";
echo "   ✓ Database relationships with foreign key constraints\n";
echo "   ✓ Automatic updates via crawler command\n\n";

// ============================================================================
// 8. WEB INTERFACE ACCESS
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 8. WEB INTERFACE                                                        │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

echo "   Start the web server:\n";
echo "   $ php artisan serve\n\n";

echo "   Then browse to:\n";
echo "   • http://127.0.0.1:8000/courses        - Course listing\n";
echo "   • http://127.0.0.1:8000/courses/CSE    - Specific course details\n";
echo "   • http://127.0.0.1:8000/api/courses    - JSON API endpoint\n\n";

// ============================================================================
// 9. DATABASE FILE INFO
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 9. DATABASE FILE INFORMATION                                            │\n";
echo "└─────────────────────────────────────────────────────────────────────────┘\n\n";

$dbPath = database_path('database.sqlite');
if (file_exists($dbPath)) {
    $size = filesize($dbPath);
    $sizeKB = round($size / 1024, 2);
    $modified = date('Y-m-d H:i:s', filemtime($dbPath));
    
    echo "   Database File: database/database.sqlite\n";
    echo "   Full Path:     {$dbPath}\n";
    echo "   File Size:     {$sizeKB} KB\n";
    echo "   Last Modified: {$modified}\n\n";
}

// ============================================================================
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         DEMONSTRATION COMPLETE                             ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "This database contains live UConn course data with:\n";
echo "• {$totalCourses} courses across multiple subjects\n";
echo "• {$totalSections} individual course sections\n";
echo "• Real-time seat availability tracking\n";
echo "• Complete course-section relationships\n";
echo "• Linked sections (labs/discussions tied to lectures)\n\n";

echo "To update data, run:\n";
echo "$ php artisan uconn:crawl --subject=ANTH --details\n\n";

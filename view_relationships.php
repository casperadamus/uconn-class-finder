<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Course;
use App\Models\Section;

echo "=== DATABASE RELATIONSHIPS VIEWER ===\n\n";

// Show database stats
$totalCourses = Course::count();
$totalSections = Section::count();

echo "DATABASE STATISTICS:\n";
echo "- Total Courses: {$totalCourses}\n";
echo "- Total Sections: {$totalSections}\n";
echo "- Average Sections per Course: " . round($totalSections / max($totalCourses, 1), 2) . "\n\n";

// Show section types breakdown
echo "SECTION TYPES:\n";
$types = Section::select('type', \DB::raw('count(*) as count'))
    ->groupBy('type')
    ->orderBy('count', 'desc')
    ->get();

foreach ($types as $type) {
    echo "- {$type->type}: {$type->count} sections\n";
}

echo "\n" . str_repeat("=", 80) . "\n\n";

// Show sample courses with relationships
echo "SAMPLE COURSES WITH RELATIONSHIPS:\n\n";

$sampleCourses = Course::with(['sections' => function($query) {
    $query->orderBy('type')->orderBy('section_number');
}])->limit(5)->get();

foreach ($sampleCourses as $course) {
    echo "COURSE: {$course->code} - {$course->title}\n";
    echo "  Campus: {$course->campus}\n";
    echo "  Credits: {$course->credits}\n";
    echo "  Total Sections: {$course->sections->count()}\n";
    
    // Group sections by type
    $lectures = $course->sections->whereIn('type', ['LEC', 'LSA']);
    $labs = $course->sections->where('type', 'LAB');
    $discussions = $course->sections->where('type', 'DIS');
    $seminars = $course->sections->whereIn('type', ['SEM', 'THE', 'IND']);
    
    if ($lectures->count() > 0) {
        echo "\n  LECTURES ({$lectures->count()}):\n";
        foreach ($lectures as $section) {
            $seats = $section->max_enrollment > 0 
                ? "{$section->seats_available}/{$section->max_enrollment}" 
                : "N/A";
            $status = $section->status == 'A' ? 'Open' : 'Full';
            echo "    - CRN {$section->crn} ({$section->type}) - Section {$section->section_number}\n";
            echo "      Time: " . ($section->meeting_time_display ?? 'TBA') . "\n";
            echo "      Seats: {$seats} - Status: {$status}\n";
        }
    }
    
    if ($labs->count() > 0) {
        echo "\n  LABS ({$labs->count()}):\n";
        foreach ($labs as $section) {
            $seats = $section->max_enrollment > 0 
                ? "{$section->seats_available}/{$section->max_enrollment}" 
                : "N/A";
            echo "    - CRN {$section->crn} - Section {$section->section_number}\n";
            echo "      Time: " . ($section->meeting_time_display ?? 'TBA') . "\n";
            echo "      Seats: {$seats}\n";
            if ($section->linked_crns) {
                echo "      Linked to: {$section->linked_crns}\n";
            }
        }
    }
    
    if ($discussions->count() > 0) {
        echo "\n  DISCUSSIONS ({$discussions->count()}):\n";
        foreach ($discussions as $section) {
            $seats = $section->max_enrollment > 0 
                ? "{$section->seats_available}/{$section->max_enrollment}" 
                : "N/A";
            echo "    - CRN {$section->crn} - Section {$section->section_number}\n";
            echo "      Time: " . ($section->meeting_time_display ?? 'TBA') . "\n";
            echo "      Seats: {$seats}\n";
        }
    }
    
    if ($seminars->count() > 0) {
        echo "\n  SEMINARS ({$seminars->count()}):\n";
        foreach ($seminars as $section) {
            $seats = $section->max_enrollment > 0 
                ? "{$section->seats_available}/{$section->max_enrollment}" 
                : "N/A";
            echo "    - CRN {$section->crn} ({$section->type}) - Section {$section->section_number}\n";
            echo "      Time: " . ($section->meeting_time_display ?? 'TBA') . "\n";
            echo "      Seats: {$seats}\n";
        }
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

// Show courses with most sections
echo "\nCOURSES WITH MOST SECTIONS:\n";
$coursesWithMostSections = Course::withCount('sections')
    ->orderBy('sections_count', 'desc')
    ->limit(10)
    ->get();

foreach ($coursesWithMostSections as $course) {
    echo "- {$course->code}: {$course->sections_count} sections\n";
}

// Show linked sections example
echo "\n" . str_repeat("=", 80) . "\n";
echo "\nLINKED SECTIONS EXAMPLE:\n";
$linkedSection = Section::whereNotNull('linked_crns')
    ->where('linked_crns', '!=', '')
    ->with('course')
    ->first();

if ($linkedSection) {
    echo "Course: {$linkedSection->course->code}\n";
    echo "Section: CRN {$linkedSection->crn} - {$linkedSection->type} {$linkedSection->section_number}\n";
    echo "Linked to: {$linkedSection->linked_crns}\n";
    
    // Try to find the linked sections
    $linkedCrns = explode(',', $linkedSection->linked_crns);
    echo "\nLinked Section Details:\n";
    foreach ($linkedCrns as $crn) {
        $crn = trim($crn);
        $linked = Section::where('crn', $crn)->first();
        if ($linked) {
            echo "  - CRN {$linked->crn}: {$linked->type} {$linked->section_number}\n";
            echo "    Time: " . ($linked->meeting_time_display ?? 'TBA') . "\n";
        }
    }
} else {
    echo "No linked sections found in database.\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "\nRELATIONSHIP STRUCTURE:\n";
echo "- courses.id (Primary Key)\n";
echo "  └─> sections.course_id (Foreign Key)\n";
echo "      └─> One course can have many sections\n";
echo "      └─> Each section belongs to one course\n\n";

echo "MODEL METHODS:\n";
echo "Course model:\n";
echo "  - \$course->sections     // All sections\n";
echo "  - \$course->lectures     // LEC and LSA sections\n";
echo "  - \$course->labs         // LAB sections\n";
echo "  - \$course->discussions  // DIS sections\n\n";

echo "Section model:\n";
echo "  - \$section->course      // Parent course\n";
echo "  - \$section->isAvailable() // Check if available\n";
echo "  - \$section->isLecture()   // Check if lecture\n";
echo "  - \$section->isLab()       // Check if lab\n";
echo "  - \$section->isDiscussion() // Check if discussion\n\n";

echo "Done!\n";

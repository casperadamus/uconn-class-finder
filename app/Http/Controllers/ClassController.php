<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClassController extends Controller
{
    protected $apiService;

    public function __construct()
    {
        $this->apiService = new \App\Services\UConnApiService();
    }

    public function index(Request $request)
{
    $campus = $request->get('campus', 'STORR@STORRS');
    $subject = $request->get('subject');
    $catalogNumber = $request->get('catalog_nbr');
    $keyword = $request->get('keyword');
    
    $classes = $this->apiService->searchClasses($campus, $subject, $catalogNumber, $keyword);
    $campuses = $this->apiService->getCampuses();
    $subjects = $this->apiService->getSubjects();
    $currentTermDisplay = $this->apiService->getCurrentTermDisplay();

    // If we have classes, get detailed seat info for the first few (for performance)
    $classesWithDetails = [];
    if (is_array($classes) && !isset($classes['error'])) {
        $classesWithDetails = $this->enhanceClassesWithSeatInfo(array_slice($classes, 0, 50)); // Limit to first 50 for performance
    } else {
        $classesWithDetails = $classes;
    }

    return view('classes.index', compact(
        'classes', 
        'classesWithDetails',
        'campuses', 
        'campus', 
        'subjects', 
        'subject',
        'currentTermDisplay', 
        'catalogNumber', 
        'keyword'
    ));
}

// New method to enhance classes with seat information
private function enhanceClassesWithSeatInfo($classes)
{
    $enhancedClasses = [];
    
    foreach ($classes as $class) {
        $courseKey = $class['key'] ?? null;
        
        if ($courseKey) {
            // Get detailed course information
            $details = $this->apiService->getCourseDetails($courseKey);
            $class['seat_info'] = $this->apiService->parseSeatInfo($details);
            $class['details'] = $details; // Store full details for debugging
        } else {
            $class['seat_info'] = 'N/A';
        }
        
        $enhancedClasses[] = $class;
        
        // Small delay to be nice to UConn's server
        usleep(100000); // 0.1 second delay
    }
    
    return $enhancedClasses;
}
}
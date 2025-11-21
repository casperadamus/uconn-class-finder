<?php

use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Facades\Route;

// Home page - redirect to courses
Route::get('/', function () {
    return redirect('/courses');
});

// Browse courses with seat data
Route::get('/courses', function () {
    $courses = Course::with(['sections' => function($query) {
        $query->orderBy('type')->orderBy('section_number');
    }])->paginate(20);
    
    return view('courses.index', compact('courses'));
});

// View specific course with all sections
Route::get('/courses/{code}', function ($code) {
    $course = Course::where('code', $code)
        ->with(['sections' => function($query) {
            $query->orderBy('type')->orderBy('section_number');
        }])
        ->firstOrFail();
    
    $lectures = $course->lectures;
    $labs = $course->labs;
    $discussions = $course->discussions;
    $seminars = $course->sections->whereIn('type', ['SEM', 'THE', 'IND']);
    
    return view('courses.show', compact('course', 'lectures', 'labs', 'discussions', 'seminars'));
});

// API endpoint for course data
Route::get('/api/courses', function () {
    $courses = Course::with('sections')
        ->when(request('subject'), function($query, $subject) {
            $query->where('subject', $subject);
        })
        ->get();
    
    return response()->json($courses);
});

// API endpoint for available sections
Route::get('/api/sections/available', function () {
    $sections = Section::where('status', 'A')
        ->where('seats_available', '>', 0)
        ->with('course')
        ->get();
    
    return response()->json($sections);
});

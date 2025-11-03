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
        $terms = $this->apiService->getTerms();

        return view('classes.index', compact(
            'classes', 
            'campuses', 
            'campus', 
            'subjects', 
            'subject',
            'terms',
            'catalogNumber',
            'keyword'
        ));
    }

    public function apiData(Request $request)
    {
        $campus = $request->get('campus', 'STORR@STORRS');
        $subject = $request->get('subject');
        
        $classes = $this->apiService->searchClasses($campus, $subject);
        
        return response()->json($classes);
    }
}
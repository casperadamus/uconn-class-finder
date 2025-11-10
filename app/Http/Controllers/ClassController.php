<?php

namespace App\Http\Controllers;

use App\Models\Course; // <-- Import your new Course model
use Illuminate\Http\Request;

/**
 * This controller handles searching for classes from
 * the local SQLite database, not the live API.
 */
class ClassSearchController extends Controller
{
    /**
     * Display the main search page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // This just returns the view that has your search form.
        // You might need to change 'search.form' to whatever
        // your Blade view file is named (e.g., 'welcome', 'search', etc.)
        return view('search.form');
    }

    /**
     * Handle the search submission, query the database,
     * and display the results.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'query' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:10',
        ]);

        $queryTerm = $request->input('query');
        
        // Start building the query on your local 'courses' table
        $queryBuilder = Course::query();

        // If the user provided a search term, search multiple columns
        if ($queryTerm) {
            $queryBuilder->where(function ($q) use ($queryTerm) {
                $q->where('title', 'like', "%{$queryTerm}%")
                  ->orWhere('subject', 'like', "%{$queryTerm}%")
                  ->orWhere('catalog_number', 'like', "%{$queryTerm}%");
            });
        }
        
        // --- Example: Add more filters ---
        // If you had a separate dropdown for 'subject'
        if ($request->has('subject')) {
             $queryBuilder->where('subject', $request->input('subject'));
        }
        
        // Eager-load the 'sections' relationship
        // This prevents the "N+1 query problem" and is very efficient
        $queryBuilder->with('sections');

        // Execute the query
        $courses = $queryBuilder->get();

        // Return the results view, passing in the courses we found
        return view('search.results', [
            'courses' => $courses,
            'searchTerm' => $queryTerm,
        ]);
    }
}
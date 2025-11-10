<?php

use App\Http\Controllers\ClassController;
use Illuminate\Support\Facades\Route;

// Make home page go directly to classes
//Route::get('/', [ClassController::class, 'index']);
Route::get('/', [ClassSearchController::class, 'index'])->name('search.index');
Route::get('/search', [ClassSearchController::class, 'search'])->name('search.results');
// Keep the classes route (so /classes still works)
Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');

// Route for getting course details - MAKE SURE THIS LINE EXISTS
Route::get('/course-details/{courseKey}', [ClassController::class, 'getCourseDetails']);
// Add this test route to debug the API
Route::get('/test-api', function () {
    $url = 'https://classes.uconn.edu/api/?page=fose&route=search&camp=STORR@STORRS';
    
    echo "<h1>Testing UConn API Directly</h1>";
    echo "<p>URL: " . $url . "</p>";
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    echo "<h2>Raw Response:</h2>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    echo "<h2>Decoded Data:</h2>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    
    echo "<h2>Response Headers:</h2>";
    $headers = get_headers($url);
    foreach ($headers as $header) {
        echo $header . "<br>";
    }


    Route::get('/debug-uconn', function () {
    // Let's try to mimic what the actual website does
    $testParams = [
        // Basic search parameters that might be needed
        'basic_search' => [
            'page' => 'fose',
            'route' => 'search',
            'camp' => 'STORR@STORRS',
            'term' => '202501', // This might be a required term code
        ],
        
        'empty_search' => [
            'page' => 'fose', 
            'route' => 'search',
            'camp' => 'STORR@STORRS',
            'subject' => '',
            'catalog_nbr' => '',
            'keyword' => '',
        ]
    ];

    $results = [];
    
    foreach ($testParams as $name => $params) {
        $url = 'https://classes.uconn.edu/api/?' . http_build_query($params);
        echo "<h3>Testing: $name</h3>";
        echo "<p>URL: <a href='$url' target='_blank'>$url</a></p>";
        
        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            echo "<pre>";
            if (is_array($data) && !empty($data)) {
                echo "SUCCESS! Got " . count($data) . " items\n";
                print_r(array_slice($data, 0, 3)); // Show first 3 items
            } else {
                echo "Response: " . $response . "\n";
            }
            echo "</pre>";
        } catch (Exception $e) {
            echo "<pre>Error: " . $e->getMessage() . "</pre>";
        }
        echo "<hr>";
    }
});
});
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class UConnApiService
{
    private $baseUrl = 'https://classes.uconn.edu/api/';

    public function searchClasses($campus = 'STORR@STORRS', $subject = null, $catalogNumber = null, $keyword = null)
    {
        try {
            
            $payload = [
                'other' => [
                    'srcdb' => '1263' // This is the term code for Spring 2026
                ],
                'criteria' => [
                    [
                        'field' => 'camp',
                        'value' => $campus
                    ]
                ]
            ];

            // Add additional search criteria if provided
            if (!empty($subject)) {
                $payload['criteria'][] = [
                    'field' => 'subject',
                    'value' => $subject
                ];
            }

            if (!empty($catalogNumber)) {
                $payload['criteria'][] = [
                    'field' => 'catalog_nbr', 
                    'value' => $catalogNumber
                ];
            }

            if (!empty($keyword)) {
                $payload['criteria'][] = [
                    'field' => 'keyword',
                    'value' => $keyword
                ];
            }

            \Log::info("UConn API Request", [
                'url_params' => 'page=fose&route=search&camp=' . urlencode($campus),
                'payload' => $payload
            ]);

            // Make the POST request with exact same format as UConn website
            $response = Http::timeout(30)
                ->retry(2, 100)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Content-Type' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Origin' => 'https://classes.uconn.edu',
                    'Referer' => 'https://classes.uconn.edu/',
                    'Sec-Fetch-Dest' => 'empty',
                    'Sec-Fetch-Mode' => 'cors',
                    'Sec-Fetch-Site' => 'same-origin'
                ])
                ->post($this->baseUrl . '?page=fose&route=search&camp=' . urlencode($campus), $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                \Log::info("UConn API Response", [
                    'status' => $response->status(),
                    'data_count' => isset($data['count']) ? $data['count'] : 'unknown',
                    'results_count' => isset($data['results']) ? count($data['results']) : 'no results'
                ]);

                // Check if we got valid results
                if (isset($data['results']) && is_array($data['results']) && !empty($data['results'])) {
                    return $data['results']; // Return just the classes array
                }
                
                return ['error' => 'No classes found in response', 'api_response' => $data];
            } else {
                return ['error' => 'HTTP Error: ' . $response->status() . ' - ' . $response->body()];
            }

        } catch (Exception $e) {
            \Log::error('UConn API Exception: ' . $e->getMessage());
            return ['error' => 'Connection error: ' . $e->getMessage()];
        }
    }

    public function getCurrentTermDisplay()
    {
        return 'Spring 2026 (Term: 1263)';
    }

    public function getSubjects()
    {
        return [
            '' => 'All Subjects',
            'AAAS' => 'Asian American Studies',
            'ACCT' => 'Accounting',
            'ANTH' => 'Anthropology', 
            'BIOL' => 'Biology',
            'CHEM' => 'Chemistry',
            'CSE' => 'Computer Science & Engineering',
            'ECON' => 'Economics',
            'ENG' => 'English',
            'MATH' => 'Mathematics',
            'PSYC' => 'Psychology',
            'HIST' => 'History',
            'PHYS' => 'Physics',
            'STAT' => 'Statistics',
        ];
    }

    public function getCampuses()
    {
        return [
            'STORR@STORRS' => 'Storrs Campus',
            'ART@ART' => 'School of Art',
            'HART' => 'Hartford Campus', 
            'STAM' => 'Stamford Campus',
            'WATERBURY' => 'Waterbury Campus',
        ];
    }

    public function getTerms()
    {
        return [
            '1263' => 'Spring 2026',
        ];
    }
}
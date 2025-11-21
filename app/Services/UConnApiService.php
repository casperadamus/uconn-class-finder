<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class UConnApiService
{
    private $baseUrl = 'https://classes.uconn.edu/api/';

    /**
     * Search for classes from the UConn API
     *
     * @param string $campus Campus code (default: STORR@STORRS)
     * @param string|null $subject Subject code (e.g., CSE, ANTH)
     * @param string|null $catalogNumber Catalog number
     * @param string|null $keyword Search keyword
     * @return array Array of class sections or error array
     */
    public function searchClasses($campus = 'STORR@STORRS', $subject = null, $catalogNumber = null, $keyword = null)
    {
        try {
            $payload = [
                'other' => [
                    'srcdb' => '1263' // Spring 2026 term code
                ],
                'criteria' => [
                    [
                        'field' => 'camp',
                        'value' => $campus
                    ]
                ]
            ];

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

            $url = $this->baseUrl . '?page=fose&route=search&camp=' . urlencode($campus);
            
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->retry(2, 100)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Content-Type' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Origin' => 'https://classes.uconn.edu',
                    'Referer' => 'https://classes.uconn.edu/',
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['results']) && is_array($data['results']) && !empty($data['results'])) {
                    return $data['results'];
                }
                
                return ['error' => 'No classes found in response', 'api_response' => $data];
            }
            
            return ['error' => 'HTTP Error: ' . $response->status() . ' - ' . $response->body()];

        } catch (Exception $e) {
            return ['error' => 'Connection error: ' . $e->getMessage()];
        }
    }

    /**
     * Get detailed information for a specific course section
     *
     * @param array $classData Class data with 'crn' and 'srcdb' fields
     * @return array Course details or error array
     */
    public function getCourseDetails($classData)
    {
        try {
            $keyValue = 'crn:' . $classData['crn'];
            
            $payload = [
                'key' => $keyValue,
                'srcdb' => $classData['srcdb'] ?? '1263'
            ];

            $url = $this->baseUrl . '?page=fose&route=details';
            
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Content-Type' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Origin' => 'https://classes.uconn.edu',
                    'Referer' => 'https://classes.uconn.edu/'
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['error' => 'API request failed: ' . $response->status()];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Parse seat information from course details
     *
     * @param array $detailsData Course details from API
     * @return array|null Parsed seat info or null
     */
    public function parseSeatInfo($detailsData)
    {
        if (!is_array($detailsData) || isset($detailsData['error'])) {
            return null;
        }

        if (isset($detailsData['seats'])) {
            $seatsHtml = $detailsData['seats'];
            
            // Extract from: "Max Enrollment: 35 / Seats Available: 0"
            if (preg_match('/Max Enrollment:\s*(\d+)\s*\/\s*Seats Available:\s*(\d+)/', $seatsHtml, $matches)) {
                return [
                    'max_enrollment' => $matches[1],
                    'available' => $matches[2],
                    'raw' => $seatsHtml
                ];
            }
        }
        
        return null;
    }

    /**
     * Get the current term display string
     *
     * @return string
     */
    public function getCurrentTermDisplay()
    {
        return 'Spring 2026 (Term: 1263)';
    }

    /**
     * Get available subjects
     *
     * @return array
     */
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
            'MCB' => 'Molecular & Cell Biology',
            'PHIL' => 'Philosophy',
            'POLS' => 'Political Science',
            'SOC' => 'Sociology',
        ];
    }

    /**
     * Get available campuses
     *
     * @return array
     */
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
}

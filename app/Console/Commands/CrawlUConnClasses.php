<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UConnApiService;

class CrawlUConnClasses extends Command
{
    protected $signature = 'uconn:crawl-classes {--campus=STORR@STORRS} {--subject=*}';
    protected $description = 'Crawl UConn classes and store them in the database';

    protected $uconnService;

    public function __construct(UConnApiService $uconnService)
    {
        parent::__construct();
        $this->uconnService = $uconnService;
    }

    public function handle()
    {
        $campus = $this->option('campus');
        $subject = $this->option('subject');

        if ($subject === '*') {
            $this->info('Fetching all available subjects...');
            $subjects = $this->uconnService->getAllSubjects();
        } else {
            $subjects = [$subject];
        }

        $this->info('Starting to crawl UConn classes...');
        $totalStored = 0;
        $totalUpdated = 0;

        foreach ($subjects as $subject) {
            $this->info("Processing subject: {$subject}");
            
            try {
                $result = $this->uconnService->searchAndStoreClasses($campus, $subject);
                
                if (isset($result['stored'])) {
                    $totalStored += $result['stored'];
                }
                if (isset($result['updated'])) {
                    $totalUpdated += $result['updated'];
                }

                $this->info("Completed processing {$subject}");
                // Add a small delay to avoid overwhelming the API
                sleep(1);
            } catch (\Exception $e) {
                $this->error("Error processing {$subject}: " . $e->getMessage());
                continue;
            }
        }

        $this->info("Crawl completed!");
        $this->info("Total classes stored: {$totalStored}");
        $this->info("Total classes updated: {$totalUpdated}");
    }
}
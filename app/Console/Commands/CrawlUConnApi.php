<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrawlUConnApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl-u-conn-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiService = new UConnApiService();
        $courses = $apiService->fetchCourses(); // Implement this method

        foreach ($courses as $course) {
            ClassCourse::updateOrCreate(
                ['course_code' => $course['code']],
                [
                    'title' => $course['title'],
                    'description' => $course['description'],
                ]
            );
        }
        $this->info('Courses crawled and saved!');
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\UConnApiService;

class UConnApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(UConnApiService::class, function ($app) {
            return new UConnApiService();
        });
    }

    public function boot()
    {
        //
    }
}
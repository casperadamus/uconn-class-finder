<?php

use App\Http\Controllers\ClassController;
use Illuminate\Support\Facades\Route;

Route::get('/classes', [ClassController::class, 'apiData']);
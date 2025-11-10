<?php

// In database/migrations/..._create_courses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            
            // --- ADD THESE THREE LINES ---
            $table->string('subject');
            $table->string('catalog_number');
            $table->string('title');
            
            $table->timestamps();
        });
    }
    
    // ... down() method ...
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create sections table
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('mpkey'); // meeting pattern key from API - not unique
            
            $table->string('crn')->unique(); // Course Reference Number (unique)
            $table->string('key'); // UConn API key (not always unique)
            $table->string('section_number'); 
            $table->string('type'); // LEC, LAB, DIS, etc.
            $table->integer('max_enrollment')->default(0); // Maximum students allowed
            $table->integer('seats_available')->default(0); // Current available seats
            $table->text('linked_crns')->nullable(); // Related sections
            $table->boolean('is_enrollment_section')->default(false);
            $table->string('instruction_mode')->nullable(); 
            $table->timestamps();
            
            // Add foreign key
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            // Add indexes
            $table->index('crn');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update courses table
        Schema::table('courses', function (Blueprint $table) {
            $table->string('code')->unique()->after('id'); // e.g., "CSE 1010"
            $table->text('description')->nullable()->after('title');
            $table->string('campus')->nullable()->after('description');
            $table->string('credits')->nullable()->after('campus');
            $table->text('prerequisites')->nullable()->after('credits');
            $table->string('term')->default('1263')->after('prerequisites'); // Spring 2026
            $table->index(['subject', 'catalog_number']);
            $table->index('term');
        });

        // Update sections table
        Schema::table('sections', function (Blueprint $table) {
            // First drop the unique constraint on class_number
            $table->dropUnique(['class_number']);
            
            // Rename class_number to mpkey (meeting pattern key from API - not unique)
            $table->renameColumn('class_number', 'mpkey');
            
            // Add new columns
            $table->string('crn')->unique()->after('id'); // Course Reference Number (unique)
            $table->string('key')->after('crn'); // UConn API key (not always unique)
            $table->string('section_number')->after('mpkey'); // e.g., "001", "002L"
            $table->string('type')->after('section_number'); // LEC, LAB, DIS, etc.
            $table->string('status')->default('A')->after('open_seats'); // A=Available, F=Full
            $table->integer('max_enrollment')->default(0)->after('status');
            $table->integer('seats_available')->default(0)->after('max_enrollment');
            $table->text('meeting_times')->nullable()->after('seats_available'); // JSON
            $table->string('meeting_days')->nullable()->after('meeting_times');
            $table->string('meeting_time_display')->nullable()->after('meeting_days'); // e.g., "MWF 10:10-11a"
            $table->text('linked_crns')->nullable()->after('meeting_time_display'); // Related sections
            $table->boolean('is_enrollment_section')->default(false)->after('linked_crns');
            $table->string('instruction_mode')->nullable()->after('is_enrollment_section'); // In Person, Online, etc.
            
            // Add indexes
            $table->index('crn');
            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn([
                'crn', 'key', 'section_number', 'type', 'status',
                'max_enrollment', 'seats_available', 'meeting_times',
                'meeting_days', 'meeting_time_display', 'linked_crns',
                'is_enrollment_section', 'instruction_mode'
            ]);
            $table->renameColumn('mpkey', 'class_number');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'description', 'campus', 'credits',
                'prerequisites', 'term'
            ]);
        });
    }
};

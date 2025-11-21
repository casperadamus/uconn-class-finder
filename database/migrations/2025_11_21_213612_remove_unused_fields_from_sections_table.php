<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['professor', 'open_seats', 'schedule', 'meeting_days']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->string('professor')->nullable();
            $table->integer('open_seats')->nullable();
            $table->string('schedule')->nullable();
            $table->string('meeting_days')->nullable();
        });
    }
};

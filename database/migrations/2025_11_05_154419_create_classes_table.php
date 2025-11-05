<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassesTable extends Migration
{
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable();
            $table->string('crn')->unique();
            $table->string('course_code'); 
            $table->string('title');
            $table->string('section');
            $table->string('campus');
            $table->string('subject'); 
            $table->string('catalog_number'); 
            $table->string('schedule_type'); 
            $table->string('status', 1); 
            $table->text('meeting_times')->nullable(); 
            $table->integer('max_enrollment')->nullable();
            $table->integer('seats_available')->nullable();
            $table->text('description')->nullable();
            $table->text('registration_restrictions')->nullable();
            $table->string('instruction_mode')->nullable(); 
            $table->text('raw_data')->nullable(); 
            $table->string('term'); 
            $table->timestamps();

            
            $table->index(['campus', 'subject']);
            $table->index(['subject', 'catalog_number']);
            $table->index('crn');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('classes');
    }
}
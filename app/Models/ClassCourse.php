<?php
// app/Models/ClassCourse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassCourse extends Model
{
    protected $table = 'classes';
    
    protected $fillable = [
        'key',
        'crn',
        'course_code',
        'title',
        'section',
        'campus',
        'subject',
        'catalog_number',
        'schedule_type',
        'status',
        'meeting_times',
        'max_enrollment',
        'seats_available',
        'description',
        'registration_restrictions',
        'instruction_mode',
        'instructor',
        'raw_data',
        'term'
    ];

    protected $casts = [
        'max_enrollment' => 'integer',
        'seats_available' => 'integer',
    ];

    // Scopes remain the same
    public function scopeOpen($query)
    {
        return $query->where('status', 'A');
    }

    public function scopeByCampus($query, $campus)
    {
        return $query->where('campus', $campus);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    public function scopeByCourseNumber($query, $catalogNumber)
    {
        return $query->where('catalog_number', $catalogNumber);
    }

    public function scopeSearchByKeyword($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('course_code', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%");
        });
    }
}
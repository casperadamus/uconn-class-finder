<?php

namespace App\Models;

// 1. ADD THIS IMPORT LINE
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    // This line is now correct
    use HasFactory;

    
    protected $fillable = ['course_id','class_number','professor', 'schedule','open_seats'];

    /**
     * Get the course that this section belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
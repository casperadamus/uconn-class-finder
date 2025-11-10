<?php

namespace App\Models;

// 1. ADD THIS LINE
use Illuminate\Database\Eloquent\Factories\HasFactory; 

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
  
    use HasFactory;

    // 2. I also fixed the spelling of "catalog_number"
    // Make sure this matches your database migration!
    protected $fillable = ['subject','catalog_number','title'];

    /**
     * Get the sections for the course.
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
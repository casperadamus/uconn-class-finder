<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'subject',
        'catalog_number',
        'title',
        'description',
        'campus',
        'credits',
        'prerequisites',
        'term'
    ];

    /**
     * Get the sections for the course.
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
    
    /**
     * Get lecture sections only
     */
    public function lectures()
    {
        return $this->hasMany(Section::class)->where('type', 'LEC');
    }
    
    /**
     * Get lab sections only
     */
    public function labs()
    {
        return $this->hasMany(Section::class)->where('type', 'LAB');
    }
    
    /**
     * Get discussion sections only
     */
    public function discussions()
    {
        return $this->hasMany(Section::class)->where('type', 'DIS');
    }
    
    /**
     * Get available sections
     */
    public function availableSections()
    {
        return $this->hasMany(Section::class)->where('status', 'A')->where('seats_available', '>', 0);
    }
}
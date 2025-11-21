<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'crn',
        'key',
        'mpkey',
        'section_number',
        'type',
        'status',
        'max_enrollment',
        'seats_available',
        'meeting_times',
        'meeting_time_display',
        'linked_crns',
        'is_enrollment_section',
        'instruction_mode'
    ];

    protected $casts = [
        'meeting_times' => 'array',
        'is_enrollment_section' => 'boolean',
    ];

    /**
     * Get the course that this section belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    /**
     * Check if section is available
     */
    public function isAvailable()
    {
        return $this->status === 'A' && $this->seats_available > 0;
    }
    
    /**
     * Check if this is a lecture section
     */
    public function isLecture()
    {
        return $this->type === 'LEC';
    }
    
    /**
     * Check if this is a lab section
     */
    public function isLab()
    {
        return $this->type === 'LAB';
    }
    
    /**
     * Check if this is a discussion section
     */
    public function isDiscussion()
    {
        return $this->type === 'DIS';
    }
    
    /**
     * Get linked sections (labs for lectures, lecture for labs)
     */
    public function getLinkedSections()
    {
        if (empty($this->linked_crns)) {
            return collect();
        }
        
        $crns = explode(',', $this->linked_crns);
        return Section::whereIn('crn', $crns)->get();
    }
}
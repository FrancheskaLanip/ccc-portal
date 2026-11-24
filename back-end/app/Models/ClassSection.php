<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// MODEL: one row of "class_sections" (a subject offered in one term, handled by one faculty).
class ClassSection extends Model
{
    protected $fillable = [
        'subject_id', 'faculty_id', 'section_name', 'school_year', 'semester', 'max_students',
        'day', 'start_time', 'end_time', 'room',
    ];

    // Include "schedule" when a section is sent as JSON to React
    protected $appends = ['schedule'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // The faculty is a user whose role is 'faculty'
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // $section->schedule gives text like "Mon 08:00-09:30, Room 101" (or "TBA" if not set)
    public function getScheduleAttribute(): string
    {
        if (! $this->day || ! $this->start_time || ! $this->end_time) {
            return 'TBA';
        }

        $text = $this->day.' '.substr($this->start_time, 0, 5).'-'.substr($this->end_time, 0, 5);

        return $this->room ? $text.', Room '.$this->room : $text;
    }
}
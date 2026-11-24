<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// MODEL: one row of "enrollments" (one student in one class section).
class Enrollment extends Model
{
    protected $fillable = ['student_id', 'class_section_id', 'status', 'enrolled_at'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class);
    }

    // The blank grade row is created by a database trigger when the enrollment is inserted
    public function grade()
    {
        return $this->hasOne(Grade::class);
    }
}

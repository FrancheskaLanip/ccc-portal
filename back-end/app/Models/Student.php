<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// MODEL: one row of the "students" table.
class Student extends Model
{
    // Mass assignment: only these columns can be filled with create()/update()
    protected $fillable = [
        'user_id', 'program_id', 'student_number', 'first_name',
        'last_name', 'year_level', 'section', 'contact_number', 'enrollment_status',
    ];

    // Each student has one login account (students.user_id -> users.id)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Each student belongs to one program (students.program_id -> programs.id)
    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}

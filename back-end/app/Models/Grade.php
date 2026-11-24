<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// MODEL: one row of "grades". "remarks" is set by a database trigger from the grade.
class Grade extends Model
{
    protected $fillable = ['enrollment_id', 'grade', 'remarks', 'encoded_by', 'encoded_at'];

    protected $casts = ['encoded_at' => 'datetime'];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}

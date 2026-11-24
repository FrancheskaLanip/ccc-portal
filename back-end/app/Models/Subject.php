<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// MODEL: one row of the "subjects" table (the course catalog).
class Subject extends Model
{
    protected $fillable = ['code', 'title', 'units'];

    // Many-to-many: a subject can be offered by many programs (pivot table program_subject)
    public function programs()
    {
        return $this->belongsToMany(Program::class)->withPivot('year_level')->withTimestamps();
    }

    public function classSections()
    {
        return $this->hasMany(ClassSection::class);
    }
}

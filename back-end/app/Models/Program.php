<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// MODEL: one row of the "programs" table (example: BSIT).
class Program extends Model
{
    // Mass assignment: only these columns can be filled with create()/update()
    protected $fillable = ['code', 'name'];

    // One program has many students (programs.id -> students.program_id)
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}

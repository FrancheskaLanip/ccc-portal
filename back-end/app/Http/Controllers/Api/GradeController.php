<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (API version): a faculty sees their own class sections and encodes grades.
// The database (row-level security) also stops a faculty from touching another faculty's grades.
class GradeController extends Controller
{
    // READ: my own sections
    public function myClasses(Request $request)
    {
        return ClassSection::with('subject:id,code,title')
            ->where('faculty_id', $request->user()->id)
            ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
            ->orderBy('school_year', 'desc')
            ->get();
    }

    // READ: the class list of one of my sections (students with their grades)
    public function classList(Request $request, ClassSection $section)
    {
        abort_unless($section->faculty_id === $request->user()->id, 403);

        return $section->enrollments()
            ->with(['student:id,student_number,first_name,last_name', 'grade'])
            ->get()
            ->sortBy(fn ($e) => $e->student->last_name)
            ->values();
    }

    // UPDATE: save one student's grade (or mark INC)
    public function update(Request $request, Grade $grade)
    {
        abort_unless($grade->enrollment->classSection->faculty_id === $request->user()->id, 403);

        $data = $request->validate([
            'grade' => ['nullable', 'numeric', 'between:1,5', 'prohibited_if:remarks,INC'],
            'remarks' => ['nullable', Rule::in(['INC'])],
        ]);

        // a database trigger sets Passed/Failed from the grade
        $grade->update([
            'grade' => $data['grade'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'encoded_by' => $request->user()->id,
            'encoded_at' => now(),
        ]);

        return $grade->fresh();
    }
}
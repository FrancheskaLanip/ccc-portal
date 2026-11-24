<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): a faculty sees their own class sections and encodes grades.
// The database (row-level security) also stops a faculty from touching another faculty's grades.
class GradeController extends Controller
{
    // READ: the faculty's own sections; if ?section=ID is given, also that section's class list
    public function index(Request $request)
    {
        $sections = ClassSection::with('subject')
            ->where('faculty_id', $request->user()->id)        // only MY sections
            ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
            ->orderBy('school_year', 'desc')
            ->get();

        $section = null;
        $enrollments = collect();

        if ($request->filled('section')) {
            $section = ClassSection::with('subject')->findOrFail($request->section);
            // a faculty cannot open someone else's section
            abort_unless($section->faculty_id === $request->user()->id, 403);

            $enrollments = $section->enrollments()->with(['student', 'grade'])->get()
                ->sortBy(fn ($e) => $e->student->last_name);
        }

        return view('grades.index', compact('sections', 'section', 'enrollments'));
    }

    // UPDATE: save one student's grade (or mark INC)
    public function update(Request $request, Grade $grade)
    {
        // the grade must belong to a section handled by this faculty
        abort_unless($grade->enrollment->classSection->faculty_id === $request->user()->id, 403);

        $data = $request->validate([
            'grade' => ['nullable', 'numeric', 'between:1,5', 'prohibited_if:remarks,INC'],
            'remarks' => ['nullable', Rule::in(['INC'])],
        ]);

        // remarks stays empty unless INC; a database trigger sets Passed/Failed from the grade
        $grade->update([
            'grade' => $data['grade'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'encoded_by' => $request->user()->id,
            'encoded_at' => now(),
        ]);

        return redirect()->route('my-classes', ['section' => $grade->enrollment->class_section_id])
            ->with('success', 'Grade saved.');
    }
}
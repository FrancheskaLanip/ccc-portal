<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): the registrar enrolls students in class sections,
// and can drop a student or re-enroll a dropped one.
class EnrollmentController extends Controller
{
    // READ: the enrollment form + the list of all enrollments
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'classSection.subject', 'classSection.faculty', 'grade'])
            ->orderByDesc('id')
            ->get();

        $students = Student::orderBy('last_name')->get();
        $sections = ClassSection::with('subject')
            ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
            ->orderBy('school_year', 'desc')
            ->get();

        return view('enrollments.index', compact('enrollments', 'students', 'sections'));
    }

    // CREATE: enroll one student in one section
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_section_id' => ['required', 'exists:class_sections,id',
                // a student cannot be enrolled twice in the same section
                Rule::unique('enrollments', 'class_section_id')->where('student_id', $request->student_id)],
        ], [
            'class_section_id.unique' => 'This student is already enrolled in that section.',
        ]);

        try {
            Enrollment::create($data);   // a database trigger also creates the blank grade row
        } catch (QueryException $e) {
            return $this->friendlyError($e);
        }

        return redirect()->route('enrollments.index')->with('success', 'Student enrolled.');
    }

    // UPDATE: drop a student, or re-enroll a dropped one
    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['enrolled', 'dropped'])]]);

        try {
            $enrollment->update(['status' => $data['status']]);
        } catch (QueryException $e) {
            return $this->friendlyError($e);
        }

        // Dropped students get the remark "Dropped"; a re-enrolled student's remark is cleared
        // (a database trigger then works out Passed/Failed again if a grade exists)
        $enrollment->grade()->update(['remarks' => $data['status'] === 'dropped' ? 'Dropped' : null]);

        return redirect()->route('enrollments.index')
            ->with('success', $data['status'] === 'dropped' ? 'Student dropped.' : 'Student re-enrolled.');
    }

    // Turn the database trigger's "section is full" error into a friendly message
    private function friendlyError(QueryException $e)
    {
        if (str_contains($e->getMessage(), 'is full')) {
            return back()->withInput()->withErrors(['class_section_id' => 'This class section is already full.']);
        }

        throw $e;   // anything else is a real problem: show it
    }
}
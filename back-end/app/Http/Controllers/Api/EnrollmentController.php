<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CONTROLLER (API version): the registrar enrolls students in class sections, drops or re-enrolls them.
class EnrollmentController extends Controller
{
    // READ: all enrollments with student, subject, section, faculty and grade
    public function index()
    {
        return Enrollment::with([
            'student:id,student_number,first_name,last_name',
            'classSection.subject:id,code,title',
            'classSection.faculty:id,name',
            'grade',
        ])->orderByDesc('id')->get();
    }

    // CREATE: enroll one student in one section (a trigger also creates the blank grade row)
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_section_id' => ['required', 'exists:class_sections,id',
                Rule::unique('enrollments', 'class_section_id')->where('student_id', $request->student_id)],
        ], [
            'class_section_id.unique' => 'This student is already enrolled in that section.',
        ]);

        try {
            $enrollment = Enrollment::create($data);
        } catch (QueryException $e) {
            $this->friendlyError($e);
        }

        return response()->json($enrollment, 201);
    }

    // UPDATE: drop a student, or re-enroll a dropped one
    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['enrolled', 'dropped'])]]);

        try {
            $enrollment->update(['status' => $data['status']]);
        } catch (QueryException $e) {
            $this->friendlyError($e);
        }

        // Dropped students get the remark "Dropped"; a re-enrolled student's remark is cleared
        $enrollment->grade()->update(['remarks' => $data['status'] === 'dropped' ? 'Dropped' : null]);

        return $enrollment;
    }

    // Turn the database trigger's "section is full" error into a validation error
    private function friendlyError(QueryException $e): never
    {
        if (str_contains($e->getMessage(), 'is full')) {
            throw ValidationException::withMessages(['class_section_id' => 'This class section is already full.']);
        }

        throw $e;
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CONTROLLER (API version): class sections (a subject offered in one term, with a schedule).
class ClassSectionController extends Controller
{
    // READ: all sections with subject, faculty and the number of seats taken
    public function index()
    {
        return ClassSection::with(['subject:id,code,title,units', 'faculty:id,name'])
            ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
            ->orderBy('school_year', 'desc')
            ->orderBy('semester')
            ->get();
    }

    // READ: the faculty accounts, for the "Faculty" dropdown of the form
    public function faculty()
    {
        return User::where('role', 'faculty')->orderBy('name')->get(['id', 'name']);
    }

    // CREATE
    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            $section = ClassSection::create($data);
        } catch (QueryException $e) {
            $this->scheduleError($e);
        }

        return response()->json($section, 201);
    }

    // UPDATE
    public function update(Request $request, ClassSection $class_section)
    {
        $data = $this->validated($request, $class_section);

        try {
            $class_section->update($data);
        } catch (QueryException $e) {
            $this->scheduleError($e);
        }

        return $class_section;
    }

    // DELETE: not allowed while students are enrolled
    public function destroy(ClassSection $class_section)
    {
        if ($class_section->enrollments()->exists()) {
            throw ValidationException::withMessages(['section' => 'Cannot delete a section that has enrollments.']);
        }

        $class_section->delete();

        return response()->noContent();
    }

    private function validated(Request $request, ?ClassSection $current = null): array
    {
        return $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'faculty_id' => ['required', Rule::exists('users', 'id')->where('role', 'faculty')],
            'section_name' => ['required', 'string', 'max:20',
                Rule::unique('class_sections', 'section_name')
                    ->where(fn ($q) => $q->where('subject_id', $request->subject_id)
                        ->where('school_year', $request->school_year)
                        ->where('semester', $request->semester))
                    ->ignore($current?->id)],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],
            'semester' => ['required', Rule::in(['1st', '2nd', 'Summer'])],
            'max_students' => ['required', 'integer', 'min:1', 'max:200'],
            'day' => ['nullable', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'])],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);
    }

    // Turn the database trigger's "Schedule conflict" error into a validation error
    private function scheduleError(QueryException $e): never
    {
        if (preg_match('/Schedule conflict: [^(\n]*/', $e->getMessage(), $match)) {
            throw ValidationException::withMessages(['schedule' => trim($match[0])]);
        }

        throw $e;
    }
}
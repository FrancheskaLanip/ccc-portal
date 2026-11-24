<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): class sections (a subject offered in one term, with a schedule).
// One page does everything: list + add form, and the same form is used to edit (?edit=ID).
class ClassSectionController extends Controller
{
    // READ: list sections with subject, faculty and how many seats are taken
    public function index(Request $request)
    {
        $sections = ClassSection::with(['subject', 'faculty'])
            // adds "enrolled_count": enrollments of this section that are still 'enrolled'
            ->withCount(['enrollments as enrolled_count' => fn ($q) => $q->where('status', 'enrolled')])
            ->orderBy('school_year', 'desc')
            ->orderBy('semester')
            ->get();

        // Dropdown data: all subjects, and only the users whose role is faculty
        $subjects = Subject::orderBy('code')->get();
        $facultyList = User::where('role', 'faculty')->orderBy('name')->get();

        $editing = $request->filled('edit') ? ClassSection::findOrFail($request->edit) : null;

        return view('class_sections.index', compact('sections', 'subjects', 'facultyList', 'editing'));
    }

    // CREATE: save a new section
    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            ClassSection::create($data);
        } catch (QueryException $e) {
            return $this->scheduleError($e);
        }

        return redirect()->route('class-sections.index')->with('success', 'Class section added.');
    }

    // UPDATE: save the changes
    public function update(Request $request, ClassSection $class_section)
    {
        $data = $this->validated($request, $class_section);

        try {
            $class_section->update($data);
        } catch (QueryException $e) {
            return $this->scheduleError($e);
        }

        return redirect()->route('class-sections.index')->with('success', 'Class section updated.');
    }

    // DELETE: only if nobody is enrolled (enrollments.class_section_id is restrictOnDelete)
    public function destroy(ClassSection $class_section)
    {
        if ($class_section->enrollments()->exists()) {
            return back()->withErrors(['section' => 'Cannot delete a section that has enrollments.']);
        }

        $class_section->delete();

        return redirect()->route('class-sections.index')->with('success', 'Class section deleted.');
    }

    // Validation shared by store and update
    private function validated(Request $request, ?ClassSection $current = null): array
    {
        return $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            // the faculty must be a user whose role is 'faculty'
            'faculty_id' => ['required', Rule::exists('users', 'id')->where('role', 'faculty')],
            // the same section name cannot be used twice for the same subject, year and semester
            'section_name' => ['required', 'string', 'max:20',
                Rule::unique('class_sections', 'section_name')
                    ->where(fn ($q) => $q->where('subject_id', $request->subject_id)
                        ->where('school_year', $request->school_year)
                        ->where('semester', $request->semester))
                    ->ignore($current?->id)],
            'school_year' => ['required', 'regex:/^\d{4}-\d{4}$/'],      // example: 2026-2027
            'semester' => ['required', Rule::in(['1st', '2nd', 'Summer'])],
            'max_students' => ['required', 'integer', 'min:1', 'max:200'],
            // schedule (optional): day, start and end time (HH:MM), room
            'day' => ['nullable', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'])],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);
    }

    // Turn the database trigger's "Schedule conflict" error into a friendly message
    private function scheduleError(QueryException $e)
    {
        if (preg_match('/Schedule conflict: [^(\n]*/', $e->getMessage(), $match)) {
            return back()->withInput()->withErrors(['schedule' => trim($match[0])]);
        }

        throw $e;   // anything else is a real problem: show it
    }
}
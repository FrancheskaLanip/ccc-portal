<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): subjects, including which programs offer each subject.
// One page does everything: list + add form, and the same form is used to edit (?edit=ID).
class SubjectController extends Controller
{
    // READ: list subjects; if ?edit=ID is in the URL, the form is filled with that subject
    public function index(Request $request)
    {
        $subjects = Subject::with('programs')->orderBy('code')->get();
        $programs = Program::orderBy('code')->get();

        $editing = $request->filled('edit') ? Subject::findOrFail($request->edit) : null;
        // ids of the programs already linked to the subject being edited (to pre-check the boxes)
        $linked = $editing ? $editing->programs()->pluck('programs.id')->all() : [];

        return view('subjects.index', compact('subjects', 'programs', 'editing', 'linked'));
    }

    // CREATE: save a new subject, then link it to the checked programs
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $subject = Subject::create($request->only('code', 'title', 'units'));
        // sync() fills the pivot table program_subject with the checked programs
        $subject->programs()->sync($data['programs'] ?? []);

        return redirect()->route('subjects.index')->with('success', 'Subject added.');
    }

    // UPDATE: save the changes and re-sync the programs
    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate($this->rules($subject));

        $subject->update($request->only('code', 'title', 'units'));
        $subject->programs()->sync($data['programs'] ?? []);

        return redirect()->route('subjects.index')->with('success', 'Subject updated.');
    }

    // DELETE: only if no class section uses it (class_sections.subject_id is restrictOnDelete)
    public function destroy(Subject $subject)
    {
        if ($subject->classSections()->exists()) {
            return back()->withErrors(['subject' => 'Cannot delete a subject that has class sections.']);
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted.');
    }

    // Validation rules shared by store and update
    private function rules(?Subject $current = null): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($current?->id)],
            'title' => ['required', 'string', 'max:255'],
            'units' => ['required', 'integer', 'between:1,6'],
            'programs' => ['nullable', 'array'],
            'programs.*' => ['integer', 'exists:programs,id'],
        ];
    }
}
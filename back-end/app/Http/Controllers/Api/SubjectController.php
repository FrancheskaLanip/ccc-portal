<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CONTROLLER (API version): subjects and the programs that offer them, answers with JSON for React.
class SubjectController extends Controller
{
    // READ: all subjects with the programs that offer them
    public function index()
    {
        return Subject::with('programs:id,code')->orderBy('code')->get();
    }

    // CREATE: save the subject, then link it to the chosen programs (pivot table program_subject)
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $subject = Subject::create($request->only('code', 'title', 'units'));
        $subject->programs()->sync($data['programs'] ?? []);

        return response()->json($subject->load('programs:id,code'), 201);
    }

    // UPDATE: save the changes and re-sync the programs
    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate($this->rules($subject));

        $subject->update($request->only('code', 'title', 'units'));
        $subject->programs()->sync($data['programs'] ?? []);

        return $subject->load('programs:id,code');
    }

    // DELETE: not allowed while a class section uses the subject
    public function destroy(Subject $subject)
    {
        if ($subject->classSections()->exists()) {
            throw ValidationException::withMessages(['subject' => 'Cannot delete a subject that has class sections.']);
        }

        $subject->delete();

        return response()->noContent();
    }

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
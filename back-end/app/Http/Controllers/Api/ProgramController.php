<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CONTROLLER (API version): CRUD for programs, answers with JSON for React.
class ProgramController extends Controller
{
    // READ: all programs with the number of students in each
    public function index()
    {
        return Program::withCount('students')->orderBy('code')->get();
    }

    // READ: one program
    public function show(Program $program)
    {
        return $program;
    }

    // CREATE: validate, then save
    public function store(Request $request)
    {
        return Program::create($request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:programs,code'],   // code must be unique
            'name' => ['required', 'string', 'max:255'],
        ]));
    }

    // UPDATE: validate, then save the changes
    public function update(Request $request, Program $program)
    {
        $program->update($request->validate([
            // unique, but ignore this program's own row
            'code' => ['required', 'string', 'max:20', Rule::unique('programs', 'code')->ignore($program->id)],
            'name' => ['required', 'string', 'max:255'],
        ]));

        return $program;
    }

    // DELETE: not allowed while the program still has students
    public function destroy(Program $program)
    {
        if ($program->students()->exists()) {
            throw ValidationException::withMessages(['program' => 'Cannot delete a program that still has students.']);
        }

        $program->delete();

        return response()->noContent();
    }
}

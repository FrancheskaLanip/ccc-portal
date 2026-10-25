<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): CRUD for programs using Blade pages.
class ProgramController extends Controller
{
    // READ: list programs with the number of students in each
    public function index()
    {
        // withCount adds a "students_count" value to every program
        $programs = Program::withCount('students')->orderBy('code')->get();

        return view('programs.index', compact('programs'));
    }

    // Show the "add program" form
    public function create()
    {
        return view('programs.create');
    }

    // CREATE: save a new program
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:programs,code'],  // code must be unique
            'name' => ['required', 'string', 'max:255'],
        ]);

        Program::create($data);   // uses $fillable in the Program model

        return redirect()->route('programs.index')->with('success', 'Program added.');
    }

    // Show the "edit program" form
    public function edit(Program $program)
    {
        return view('programs.edit', compact('program'));
    }

    // UPDATE: save the changes to a program
    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            // unique, but ignore this program's own row
            'code' => ['required', 'string', 'max:20', Rule::unique('programs', 'code')->ignore($program->id)],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $program->update($data);

        return redirect()->route('programs.index')->with('success', 'Program updated.');
    }

    // DELETE: remove a program (only if it has no students)
    public function destroy(Program $program)
    {
        // students.program_id uses restrictOnDelete, so check first for a friendly error
        if ($program->students()->exists()) {
            return back()->withErrors(['program' => 'Cannot delete a program that still has students.']);
        }

        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Program deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('students')->orderBy('code')->get();

        return view('programs.index', compact('programs'));
    }

    public function create()
    {
        return view('programs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:programs,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Program::create($data);

        return redirect()->route('programs.index')->with('success', 'Program added.');
    }

    public function edit(Program $program)
    {
        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('programs', 'code')->ignore($program->id)],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $program->update($data);

        return redirect()->route('programs.index')->with('success', 'Program updated.');
    }

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
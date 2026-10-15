<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('program')->orderBy('last_name')->get();

        return view('students.index', compact('students'));
    }

    public function create()
    {
        $programs = Program::orderBy('code')->get();

        return view('students.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'program_id' => ['required', 'exists:programs,id'],
            'student_number' => ['required', 'string', 'max:50', 'unique:students,student_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'contact_number' => ['nullable', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'enrollment_status' => ['required', Rule::in(['Not Enrolled', 'Pending', 'Enrolled'])],
        ]);

        // Create the login account and the student record together, or neither
        DB::transaction(function () use ($data) {
            $user = new User();
            $user->name = $data['first_name'] . ' ' . $data['last_name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = 'student';
            $user->save();

            $user->student()->create(Arr::except($data, ['email', 'password']));
        });

        return redirect()->route('students.index')->with('success', 'Student added.');
    }

    public function edit(Student $student)
    {
        $programs = Program::orderBy('code')->get();

        return view('students.edit', compact('student', 'programs'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'student_number' => ['required', 'string', 'max:50', Rule::unique('students', 'student_number')->ignore($student->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'contact_number' => ['nullable', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'enrollment_status' => ['required', Rule::in(['Not Enrolled', 'Pending', 'Enrolled'])],
        ]);

        $student->update($data);
        $student->user->update(['name' => $data['first_name'] . ' ' . $data['last_name']]);

        return redirect()->route('students.index')->with('success', 'Student updated.');
    }

    public function destroy(Student $student)
    {
        // Deleting the user also removes the student row (ON DELETE CASCADE)
        $student->user->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted.');
    }

    // Registrar confirms a student's enrollment
    public function enroll(Student $student)
    {
        $student->update(['enrollment_status' => 'Enrolled']);

        return back()->with('success', 'Student officially enrolled.');
    }
}
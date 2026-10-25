<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Support\Credentials;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): CRUD for students using Blade pages.
class StudentController extends Controller
{
    // READ: list all students, sorted by last name
    public function index()
    {
        // with('program') loads each student's program in one extra query (avoids many small queries)
        $students = Student::with('program')->orderBy('last_name')->get();

        return view('students.index', compact('students'));
    }

    // Show the "add student" form (the program list fills the dropdown)
    public function create()
    {
        $programs = Program::orderBy('code')->get();

        return view('students.create', compact('programs'));
    }

    // CREATE: save a new student and their login account
    public function store(Request $request)
    {
        // Validation rules. If one fails, Laravel goes back to the form with error messages
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],              // no duplicate emails
            'program_id' => ['required', 'exists:programs,id'],                             // program must exist
            'student_number' => ['required', 'string', 'regex:/^CCC-\d{4}-\d{4}$/', 'unique:students,student_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'section' => ['nullable', 'string', 'max:10'],
            'contact_number' => ['nullable', 'regex:/^[0-9+\-\s]{7,20}$/'],                 // digits, +, - and spaces only
            'enrollment_status' => ['required', Rule::in(['Not Enrolled', 'Pending', 'Enrolled'])],
        ]);

        // A random password nobody knows. The student gets real credentials
        // by email the first time they use "Forgot password".
        $password = Credentials::generatePassword();

        // Create the login account and the student record together, or neither
        DB::transaction(function () use ($data, $password) {
            $user = new User();
            $user->name = $data['first_name'] . ' ' . $data['last_name'];
            $user->email = $data['email'];
            $user->password = Hash::make($password);
            $user->role = 'student'; // set in code, never from the form
            $user->save();

            // Save the student linked to this user (everything except the email)
            $user->student()->create(Arr::except($data, ['email']));
        });

        // No email here (see PasswordRecovery for the first-time credentials email).

        return redirect()->route('students.index')
            ->with('success', 'Student added. They can get their login credentials with "Forgot password" on the login page.');
    }

    // Show the "edit student" form
    public function edit(Student $student)
    {
        $programs = Program::orderBy('code')->get();

        return view('students.edit', compact('student', 'programs'));
    }

    // UPDATE: save the changes to a student
    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            // unique, but ignore this student's own row so saving without changes works
            'student_number' => ['required', 'string', 'regex:/^CCC-\d{4}-\d{4}$/', Rule::unique('students', 'student_number')->ignore($student->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'section' => ['nullable', 'string', 'max:10'],
            'contact_number' => ['nullable', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'enrollment_status' => ['required', Rule::in(['Not Enrolled', 'Pending', 'Enrolled'])],
        ]);

        $student->update($data);
        // Keep the account name the same as the student name
        $student->user->update(['name' => $data['first_name'] . ' ' . $data['last_name']]);

        return redirect()->route('students.index')->with('success', 'Student updated.');
    }

    // DELETE: remove a student and their login
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

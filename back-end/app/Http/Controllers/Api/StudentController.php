<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Support\Credentials;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// CONTROLLER (API version): CRUD for students, answers with JSON for React.
class StudentController extends Controller
{
    // Validation rules shared by store() and update() (DRY: written once)
    private function rules(?Student $student = null): array
    {
        return [
            'program_id' => ['required', 'exists:programs,id'],   // program must exist
            // unique, but ignore this student's own row when editing
            'student_number' => ['required', 'string', 'regex:/^CCC-\d{4}-\d{4}$/', Rule::unique('students', 'student_number')->ignore($student?->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'section' => ['nullable', 'string', 'max:10'],
            'contact_number' => ['nullable', 'regex:/^[0-9+\-\s]{7,20}$/'],   // digits, +, - and spaces only
            'enrollment_status' => ['required', Rule::in(['Not Enrolled', 'Pending', 'Enrolled'])],
        ];
    }

    // READ: all students, sorted by last name (with their program)
    public function index()
    {
        return Student::with('program')->orderBy('last_name')->get();
    }

    // READ: one student
    public function show(Student $student)
    {
        return $student;
    }

    // The logged-in student's own record (for the dashboard)
    public function mine(Request $request)
    {
        return $request->user()->student()->with('program')->first();
    }

    // CREATE: save a new student and their login account
    public function store(Request $request)
    {
        // Same rules as update, plus a unique email for the new account
        $data = $request->validate($this->rules() + [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        // A random password nobody knows. The student gets real credentials
        // by email the first time they use "Forgot password".
        $password = Credentials::generatePassword();

        // Create the login account and the student record together, or neither
        $user = DB::transaction(function () use ($data, $password) {
            $user = new User();
            $user->name = $data['first_name'] . ' ' . $data['last_name'];
            $user->email = $data['email'];
            $user->password = Hash::make($password);
            $user->role = 'student'; // set in code, never from the form
            $user->save();

            // Save the student linked to this user (everything except the email)
            $user->student()->create(Arr::except($data, ['email']));

            return $user;
        });

        // No email here (see PasswordRecovery for the first-time credentials email).

        return response()->json($user->student, 201);   // 201 = created
    }

    // UPDATE: save the changes to a student
    public function update(Request $request, Student $student)
    {
        $data = $request->validate($this->rules($student));

        $student->update($data);
        // Keep the account name the same as the student name
        $student->user->update(['name' => $data['first_name'] . ' ' . $data['last_name']]);

        return $student;
    }

    // DELETE: remove the student and their login
    public function destroy(Student $student)
    {
        $student->user->delete(); // cascades to the student row

        return response()->noContent();
    }

    // Registrar confirms a student's enrollment
    public function enroll(Student $student)
    {
        $student->update(['enrollment_status' => 'Enrolled']);

        return $student;
    }
}

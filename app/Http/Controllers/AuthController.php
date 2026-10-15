<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $portal = $request->query('portal', 'student');
        abort_unless(in_array($portal, ['student', 'faculty', 'staff']), 404);

        return view('auth.login', compact('portal'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'portal' => ['required', Rule::in(['student', 'faculty', 'staff'])],
        ]);

        // Which roles may use which login option
        $allowed = [
            'student' => ['student'],
            'faculty' => ['faculty'],
            'staff' => ['registrar', 'admin'],
        ];

        if (Auth::attempt($request->only('email', 'password'))) {
            if (! in_array($request->user()->role, $allowed[$request->portal])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login', ['portal' => $request->portal])
                    ->withErrors(['email' => 'This account cannot log in here. Choose the correct login option.']);
            }

            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email', 'portal');
    }

    public function showRegister()
    {
        $programs = Program::orderBy('code')->get();

        return view('auth.register', compact('programs'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'student_number' => ['required', 'string', 'max:50', 'unique:students,student_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'program_id' => ['required', 'exists:programs,id'],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'contact_number' => ['nullable', 'regex:/^[0-9+\-\s]{7,20}$/'],
        ]);

        // Create the login and the student record together, or neither
        $user = DB::transaction(function () use ($data) {
            $user = new User();
            $user->name = $data['first_name'] . ' ' . $data['last_name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->role = 'student'; // set in code, never from the form
            $user->save();

            $user->student()->create([
                'program_id' => $data['program_id'],
                'student_number' => $data['student_number'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'year_level' => $data['year_level'],
                'contact_number' => $data['contact_number'] ?? null,
                'enrollment_status' => 'Pending', // registrar confirms later
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')
            ->with('success', 'Enrollment finished. Wait for the registrar to officially enroll you.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
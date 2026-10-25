<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): handles login and logout for the Blade pages.
class AuthController extends Controller
{
    // Show the login form for the chosen option (?portal=student|faculty|staff)
    public function showLogin(Request $request)
    {
        $portal = $request->query('portal', 'student');
        // Any other value in the URL -> 404 page
        abort_unless(in_array($portal, ['student', 'faculty', 'staff']), 404);

        return view('auth.login', compact('portal'));
    }

    // Check the login form when it is submitted
    public function login(Request $request)
    {
        // Validation: stop here and show errors if a field is missing or wrong
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
            'portal' => ['required', Rule::in(['student', 'faculty', 'staff'])],
        ]);

        // Which roles may use which login option
        $allowed = [
            'student' => ['student'],
            'faculty' => ['faculty'],
            'staff' => ['registrar', 'admin'],
        ];

        // Students log in with their student number, everyone else with email
        if ($request->portal === 'student') {
            // Find the student, then use the email of their user account
            $student = Student::with('user')->where('student_number', $request->login)->first();
            $email = $student?->user?->email;
        } else {
            $email = $request->login;
        }

        // Auth::attempt checks the email and the hashed password
        if ($email && Auth::attempt(['email' => $email, 'password' => $request->password])) {
            // Correct password, but is this role allowed on this login option?
            if (! in_array($request->user()->role, $allowed[$request->portal])) {
                // Wrong option: log the user out again
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login', ['portal' => $request->portal])
                    ->withErrors(['login' => 'This account cannot log in here. Choose the correct login option.']);
            }

            // New session id after login (protects against session fixation)
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // Same message for a wrong email or a wrong password (does not reveal which one)
        return back()->withErrors(['login' => 'Invalid login or password.'])->onlyInput('login', 'portal');
    }

    // Log out: clear the session and go back to the login page
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();       // destroy the session
        $request->session()->regenerateToken();  // new CSRF token

        return redirect('/login');
    }
}

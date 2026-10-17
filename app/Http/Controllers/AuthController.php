<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $student = Student::with('user')->where('student_number', $request->login)->first();
            $email = $student?->user?->email;
        } else {
            $email = $request->login;
        }

        if ($email && Auth::attempt(['email' => $email, 'password' => $request->password])) {
            if (! in_array($request->user()->role, $allowed[$request->portal])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login', ['portal' => $request->portal])
                    ->withErrors(['login' => 'This account cannot log in here. Choose the correct login option.']);
            }

            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['login' => 'Invalid login or password.'])->onlyInput('login', 'portal');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
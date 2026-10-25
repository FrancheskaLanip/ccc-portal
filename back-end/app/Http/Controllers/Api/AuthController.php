<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Support\PasswordRecovery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CONTROLLER (API version): login, logout and passwords for the React site.
// It answers with JSON instead of HTML pages.
class AuthController extends Controller
{
    // Who is logged in right now? (React calls this when the page loads)
    public function me(Request $request)
    {
        return response()->json($request->user()); // "null" when logged out
    }

    // Check the login sent by React
    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
            'portal' => ['required', Rule::in(['student', 'faculty', 'staff'])],
        ]);

        // Which roles may use which login option
        $allowed = ['student' => ['student'], 'faculty' => ['faculty'], 'staff' => ['registrar', 'admin']];

        // Students log in with their student number, everyone else with email
        $email = $request->portal === 'student'
            ? Student::with('user')->where('student_number', $request->login)->first()?->user?->email
            : $request->login;

        // Unknown login or wrong password -> same error message for both
        if (! $email || ! Auth::attempt(['email' => $email, 'password' => $request->password])) {
            throw ValidationException::withMessages(['login' => 'Invalid login or password.']);
        }

        // Correct password but wrong login option -> log out again
        if (! in_array($request->user()->role, $allowed[$request->portal])) {
            Auth::logout();
            throw ValidationException::withMessages(['login' => 'This account cannot log in here. Choose the correct login option.']);
        }

        // New session id after login (protects against session fixation)
        $request->session()->regenerate();

        return $request->user();   // React saves this user (name, role)
    }

    // Log out and destroy the session
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();   // empty 204 response
    }

    // Forgot password: the user sent their email
    public function forgot(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // First time: login credentials. After that: a reset code.
        PasswordRecovery::request($request->email);

        // Same message whether or not the email exists
        return ['message' => 'If the email is registered, we sent an email to it. Check your inbox.'];
    }

    // Reset password: the user sent the 6-digit code and a new password
    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if (! PasswordRecovery::reset($data['email'], $data['code'], $data['password'])) {
            throw ValidationException::withMessages(['code' => 'The code is wrong or has expired. Ask for a new one.']);
        }

        return ['message' => 'Password changed. You can log in now.'];
    }

    // Change password while logged in
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],   // must match the old password
            'password' => ['required', 'confirmed', 'min:8', 'different:current_password'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);   // never store a plain password
        $user->save();

        return response()->noContent();
    }
}

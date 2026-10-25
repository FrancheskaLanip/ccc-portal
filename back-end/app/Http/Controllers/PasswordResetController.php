<?php

namespace App\Http\Controllers;

use App\Support\PasswordRecovery;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// CONTROLLER (web version): Forgot password pages. The real logic is in PasswordRecovery.
class PasswordResetController extends Controller
{
    // Show the "enter your email" form
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    // The user submitted their email
    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // First time: login credentials. After that: a reset code.
        PasswordRecovery::request($request->email);

        // Same message whether or not the email exists
        return redirect()->route('password.reset', ['email' => $request->email])
            ->with('success', 'If the email is registered, we sent an email to it. Check your inbox. If it contains a code, enter it below.');
    }

    // Show the form for the 6-digit code and new password
    public function showReset(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->query('email', '')]);
    }

    // The user submitted the code and the new password
    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // reset() returns false if the code is wrong, expired, or tried too many times
        if (! PasswordRecovery::reset($data['email'], $data['code'], $data['password'])) {
            throw ValidationException::withMessages(['code' => 'The code is wrong or has expired. Ask for a new one.']);
        }

        return redirect()->route('login')->with('success', 'Password changed. You can log in now.');
    }
}

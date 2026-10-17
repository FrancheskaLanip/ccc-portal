<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Credentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::with('student')->where('email', $request->email)->first();

        if ($user) {
            // Students log in with their student number, everyone else with email
            $username = $user->role === 'student' && $user->student
                ? $user->student->student_number
                : $user->email;

            $password = Credentials::generatePassword();
            $user->password = Hash::make($password);
            $user->save();

            Credentials::send($user, $username, $password);
        }

        // Same message whether or not the email exists
        return back()->with('success', 'If the email is registered, new login credentials were sent to it.');
    }
}
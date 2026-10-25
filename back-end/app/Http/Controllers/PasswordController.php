<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// CONTROLLER: a logged-in user changes their own password.
class PasswordController extends Controller
{
    // Show the change-password form
    public function edit()
    {
        return view('auth.change-password');
    }

    // Save the new password
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],            // must match the old password
            'password' => ['required', 'confirmed', 'min:8', 'different:current_password'], // confirmed = needs password_confirmation field
        ]);

        $user = $request->user();                       // the logged-in user
        $user->password = Hash::make($request->password); // never store a plain password
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Password changed.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Credentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// CONTROLLER (web version): the admin manages staff and faculty accounts.
class UserController extends Controller
{
    // READ: list all accounts except students
    public function index()
    {
        $users = User::where('role', '!=', 'student')->orderBy('role')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    // Show the "create account" form
    public function create()
    {
        return view('users.create');
    }

    // CREATE: save a new faculty, registrar or admin account
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['faculty', 'registrar', 'admin'])],   // students are made in the Students page
        ]);

        // A random password nobody knows. The person gets real credentials
        // by email the first time they use "Forgot password".
        $password = Credentials::generatePassword();

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($password);
        $user->role = $data['role']; // only an admin can reach this route
        $user->save();

        // No email here (see PasswordRecovery for the first-time credentials email).

        return redirect()->route('users.index')
            ->with('success', 'Account created. They can get their login credentials with "Forgot password" on the login page.');
    }

    // DELETE: remove an account
    public function destroy(User $user)
    {
        // An admin must not delete themselves
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Account deleted.');
    }
}

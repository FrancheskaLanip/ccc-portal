<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Credentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// CONTROLLER (API version): the admin manages staff and faculty accounts.
class UserController extends Controller
{
    // READ: all accounts except students
    public function index()
    {
        return User::where('role', '!=', 'student')->orderBy('role')->orderBy('name')->get();
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
        $user->role = $data['role'];
        $user->save();

        // No email here (see PasswordRecovery for the first-time credentials email).

        return response()->json($user, 201);   // 201 = created
    }

    // DELETE: remove an account
    public function destroy(User $user)
    {
        // An admin must not delete themselves
        if ($user->id === Auth::id()) {
            throw ValidationException::withMessages(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// CONTROLLER: prepares the data for the dashboard page (so the view has no logic).
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();   // the logged-in user

        // Only students have a student record (with their program)
        $student = $user->role === 'student'
            ? $user->student()->with('program')->first()
            : null;

        return view('dashboard', compact('student'));
    }
}

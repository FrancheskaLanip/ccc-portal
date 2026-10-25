<?php

// ROUTES: this file maps each URL to the code that handles it.
// Flow of a request: route -> middleware -> controller -> model -> view.

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// The home page sends everyone to the login page
Route::get('/', fn () => redirect('/login'));

// Pages for people who are NOT logged in yet ("guest" middleware)
Route::middleware('guest')->group(function () {
    // Show the login form, then check the login when it is submitted
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Forgot password: ask for the email (throttle = max 6 tries per minute)
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->middleware('throttle:6,1')->name('password.email');
    // Reset password: enter the 6-digit code and the new password
    Route::get('/reset-password', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.reset.update');
});

// Pages only for logged-in users ("auth" middleware)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // The dashboard needs data (a student's record), so a controller prepares it
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Any logged-in user can change their own password
    Route::get('/password/change', [PasswordController::class, 'edit'])->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'update'])->name('password.change.update');

    // Role protection: "role:admin,registrar" means only those roles may enter
    Route::get('/staff', fn () => view('staff'))
        ->middleware('role:admin,registrar')->name('staff');

    Route::get('/admin', fn () => view('admin'))
        ->middleware('role:admin')->name('admin');

    // Admin and registrar: manage programs and students (full CRUD)
    Route::middleware('role:admin,registrar')->group(function () {
        // resource() creates index, create, store, edit, update, destroy routes at once
        Route::resource('programs', ProgramController::class)->except('show');
        Route::resource('students', StudentController::class)->except('show');
        // Registrar confirms that a student is officially enrolled
        Route::patch('/students/{student}/enroll', [StudentController::class, 'enroll'])->name('students.enroll');
    });

    // Admin only: manage user accounts (list, create, delete)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'destroy']);
    });
});
// ---- JSON API used by the React frontend ----
use App\Http\Controllers\Api;

// Every URL here starts with /api (example: /api/students)
Route::prefix('api')->group(function () {
    // Open routes: no login needed
    Route::get('/me', [Api\AuthController::class, 'me']);          // who is logged in?
    Route::post('/login', [Api\AuthController::class, 'login']);
    Route::post('/forgot-password', [Api\AuthController::class, 'forgot'])->middleware('throttle:6,1');
    Route::post('/reset-password', [Api\AuthController::class, 'reset'])->middleware('throttle:6,1');

    // Routes that need a login
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [Api\AuthController::class, 'logout']);
        Route::post('/password/change', [Api\AuthController::class, 'changePassword']);
        Route::get('/my-student', [Api\StudentController::class, 'mine']);   // a student's own record

        // Admin and registrar only
        Route::middleware('role:admin,registrar')->group(function () {
            // apiResource() = index, store, show, update, destroy (JSON, no HTML forms)
            Route::apiResource('programs', Api\ProgramController::class);
            Route::apiResource('students', Api\StudentController::class);
            Route::patch('/students/{student}/enroll', [Api\StudentController::class, 'enroll']);
        });

        // Admin only
        Route::middleware('role:admin')->apiResource('users', Api\UserController::class)->only(['index', 'store', 'destroy']);
    });
});

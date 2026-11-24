<?php

// ROUTES: this file maps each URL to the code that handles it.
// Flow of a request: route -> middleware -> controller -> model -> view.

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
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

    // Everyone logged in can read announcements (what they see is filtered by the database)
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    // Admin and registrar: manage programs, students, subjects, class sections, enrollments and announcements
    Route::middleware('role:admin,registrar')->group(function () {
        // resource() creates index, create, store, edit, update, destroy routes at once
        Route::resource('programs', ProgramController::class)->except('show');
        Route::resource('students', StudentController::class)->except('show');
        // Registrar confirms that a student is officially enrolled
        Route::patch('/students/{student}/enroll', [StudentController::class, 'enroll'])->name('students.enroll');

        // Subjects and class sections (one page each: list + add/edit form)
        Route::resource('subjects', SubjectController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('class-sections', ClassSectionController::class)->only(['index', 'store', 'update', 'destroy']);

        // Enrollments: enroll a student in a section, drop or re-enroll
        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'store', 'update']);

        // Post, edit and delete announcements
        Route::resource('announcements', AnnouncementController::class)->only(['store', 'update', 'destroy']);
    });

    // Faculty only: see my own classes and encode grades
    Route::middleware('role:faculty')->group(function () {
        Route::get('/my-classes', [GradeController::class, 'index'])->name('my-classes');
        Route::put('/grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
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

        // Everyone logged in can read announcements (the database filters what each role sees)
        Route::get('/announcements', [Api\AnnouncementController::class, 'index'])->name('api.announcements.index');

        // Student only: my subjects with schedule and grades
        Route::get('/my-subjects', [Api\StudentController::class, 'subjects'])->middleware('role:student');

        // Faculty only: my classes and grade encoding
        Route::middleware('role:faculty')->group(function () {
            Route::get('/my-classes', [Api\GradeController::class, 'myClasses']);
            Route::get('/my-classes/{section}', [Api\GradeController::class, 'classList']);
            Route::put('/grades/{grade}', [Api\GradeController::class, 'update']);
        });

        // Admin and registrar only
        Route::middleware('role:admin,registrar')->group(function () {
            // apiResource() = index, store, show, update, destroy (JSON, no HTML forms)
            Route::apiResource('programs', Api\ProgramController::class)->names('api.programs');
            Route::apiResource('students', Api\StudentController::class)->names('api.students');
            Route::patch('/students/{student}/enroll', [Api\StudentController::class, 'enroll']);

            Route::apiResource('subjects', Api\SubjectController::class)->except('show')->names('api.subjects');
            Route::apiResource('class-sections', Api\ClassSectionController::class)->except('show')->names('api.class-sections');
            Route::get('/faculty', [Api\ClassSectionController::class, 'faculty']);
            Route::apiResource('enrollments', Api\EnrollmentController::class)->only(['index', 'store', 'update'])->names('api.enrollments');
            Route::apiResource('announcements', Api\AnnouncementController::class)->only(['store', 'update', 'destroy'])->names('api.announcements');
        });

        // Admin only
        Route::middleware('role:admin')->apiResource('users', Api\UserController::class)->only(['index', 'store', 'destroy'])->names('api.users');
    });
});
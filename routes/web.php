<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/staff', fn () => view('staff'))
        ->middleware('role:admin,registrar')->name('staff');

    Route::get('/admin', fn () => view('admin'))
        ->middleware('role:admin')->name('admin');

    Route::get('/faculty', fn () => view('faculty'))
        ->middleware('role:faculty')->name('faculty');

    Route::middleware('role:admin,registrar')->group(function () {
        Route::resource('programs', ProgramController::class)->except('show');
        Route::resource('students', StudentController::class)->except('show');
        Route::patch('/students/{student}/enroll', [StudentController::class, 'enroll'])->name('students.enroll');
    });
});
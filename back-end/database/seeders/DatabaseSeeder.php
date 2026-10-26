<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// SEEDER: fills an empty database with test data (php artisan migrate:fresh --seed).
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // forceCreate skips mass-assignment protection, so we can set the role here.
        // credentials_sent_at is also set on every test account: their password is already
        // known, so the first "Forgot password" sends a 6-digit code instead of new credentials.
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@ccc.test',
            'password' => Hash::make('password'),   // test password is "password"
            'role' => 'admin',
            'credentials_sent_at' => now(),
        ]);

        User::forceCreate([
            'name' => 'Registrar',
            'email' => 'registrar@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'registrar',
            'credentials_sent_at' => now(),
        ]);

        User::forceCreate([
            'name' => 'Faculty',
            'email' => 'faculty@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'credentials_sent_at' => now(),
        ]);

        // Two sample programs
        $bspa = Program::create([
            'code' => 'BSPA',
            'name' => 'Bachelor of Science in Public Administration',
        ]);

        Program::create([
            'code' => 'BSM',
            'name' => 'Bachelor of Science in Midwifery',
        ]);

        // Test student (logs in with the student number)
        $studentUser = User::forceCreate([
            'name' => 'Juan Dela Cruz',
            'email' => 'student@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'credentials_sent_at' => now(),
        ]);

        // Create the student record linked to this account (user_id is filled in automatically)
        $studentUser->student()->create([
            'program_id' => $bspa->id,
            'student_number' => 'CCC-2026-0001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'year_level' => 1,
            'section' => 'A',
            'enrollment_status' => 'Enrolled',
        ]);
    }
}
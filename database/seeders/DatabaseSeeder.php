<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::forceCreate([
            'name' => 'Registrar',
            'email' => 'registrar@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'registrar',
        ]);

        User::forceCreate([
            'name' => 'Faculty',
            'email' => 'faculty@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'faculty',
        ]);

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
        ]);

        $studentUser->student()->create([
            'program_id' => $bspa->id,
            'student_number' => '2026-0001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'year_level' => 1,
            'section' => 'A',
            'enrollment_status' => 'Enrolled',
        ]);
    }
}
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

        Program::create([
            'code' => 'BSIT',
            'name' => 'Bachelor of Science in Information Technology',
        ]);
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MIGRATION: adds the optional "section" column (example: A) to students.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Up to 10 characters, optional, placed right after year_level
            $table->string('section', 10)->nullable()->after('year_level');
        });
    }

    // Undo: remove the column
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }
};

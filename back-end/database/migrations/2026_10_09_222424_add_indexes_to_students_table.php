<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MIGRATION: adds indexes so searching and sorting students is faster.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('program_id');   // speeds up "students of a program" and the join to programs
            $table->index('last_name');    // speeds up ORDER BY last_name (the student list is sorted by it)
        });
    }

    // Undo: remove the indexes
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['program_id']);
            $table->dropIndex(['last_name']);
        });
    }
};

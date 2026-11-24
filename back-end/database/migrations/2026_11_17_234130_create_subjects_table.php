<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// MIGRATION: "subjects" = the course catalog (one row per subject, e.g. GE101).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();      // e.g. GE101 (no duplicates)
            $table->string('title');
            $table->unsignedSmallInteger('units');
            $table->timestamps();
        });

        // The database itself refuses a subject with 0 or more than 6 units
        DB::statement('ALTER TABLE subjects ADD CONSTRAINT subjects_units_check CHECK (units BETWEEN 1 AND 6)');
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
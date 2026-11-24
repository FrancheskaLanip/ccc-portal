<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// MIGRATION: "class_sections" = a subject offered in one term, with a faculty and a seat limit.
// Faculty are rows of "users" with role 'faculty', so faculty_id points to users.id.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('faculty_id')->constrained('users')->restrictOnDelete();
            $table->string('section_name');                 // e.g. A
            $table->string('school_year');                  // e.g. 2026-2027
            $table->string('semester');                     // 1st, 2nd, Summer
            $table->unsignedSmallInteger('max_students')->default(40);
            $table->timestamps();

            // No duplicate section of the same subject in the same term
            $table->unique(['subject_id', 'section_name', 'school_year', 'semester']);
            $table->index('faculty_id');
        });

        DB::statement("ALTER TABLE class_sections ADD CONSTRAINT class_sections_semester_check CHECK (semester IN ('1st', '2nd', 'Summer'))");
        DB::statement('ALTER TABLE class_sections ADD CONSTRAINT class_sections_max_check CHECK (max_students > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};

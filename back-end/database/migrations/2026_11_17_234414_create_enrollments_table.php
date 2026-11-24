<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// MIGRATION: "enrollments" = one student enrolled in one class section.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('enrolled');          // enrolled | dropped
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            // A student cannot enroll twice in the same section
            $table->unique(['student_id', 'class_section_id']);
            // Indexes for the two common lookups: "my subjects" and "class list"
            $table->index('student_id');
            $table->index('class_section_id');
        });

        DB::statement("ALTER TABLE enrollments ADD CONSTRAINT enrollments_status_check CHECK (status IN ('enrolled', 'dropped'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};

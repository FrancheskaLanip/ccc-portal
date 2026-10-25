<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MIGRATION: creates the students table. It links to users and programs with foreign keys.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            // One student = one account (unique). If the user is deleted, the student row is deleted too (cascade)
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // A program cannot be deleted while students still use it (restrict)
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('student_number')->unique();    // no duplicate student numbers
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedSmallInteger('year_level');    // positive whole number
            $table->string('contact_number')->nullable();  // nullable = optional
            $table->string('enrollment_status')->default('Not Enrolled');
            $table->timestamps();
        });
    }

    // Undo: delete the table
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

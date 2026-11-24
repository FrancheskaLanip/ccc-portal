<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MIGRATION: pivot table for the many-to-many link between programs and subjects.
// One subject can belong to many programs (shared subjects), and one program has many subjects.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year_level')->nullable();   // when the program usually offers it
            $table->timestamps();

            // The same subject cannot be linked twice to the same program
            $table->unique(['program_id', 'subject_id']);
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_subject');
    }
};

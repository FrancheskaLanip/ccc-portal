<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// MIGRATION: "grades" = the grade of one enrollment (one-to-one with enrollments).
// grade is numeric (1.00 best to 5.00 worst); remarks is Passed, Failed, INC or Dropped.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('grade', 3, 2)->nullable();             // empty until the faculty encodes it
            $table->string('remarks')->nullable();
            $table->foreignId('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('encoded_at')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE grades ADD CONSTRAINT grades_grade_check CHECK (grade IS NULL OR grade BETWEEN 1.00 AND 5.00)');
        DB::statement("ALTER TABLE grades ADD CONSTRAINT grades_remarks_check CHECK (remarks IS NULL OR remarks IN ('Passed', 'Failed', 'INC', 'Dropped'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
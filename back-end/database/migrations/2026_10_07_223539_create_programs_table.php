<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MIGRATION: creates the programs table (example: BSPA, BSM).
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();                        // primary key
            $table->string('code')->unique();    // short code, no duplicates
            $table->string('name');
            $table->timestamps();
        });
    }

    // Undo: delete the table
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};

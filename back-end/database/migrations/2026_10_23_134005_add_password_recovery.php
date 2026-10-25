<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// MIGRATION: supports the Forgot password feature.
return new class extends Migration
{
    public function up(): void
    {
        // Set the first time the system emails the user their login credentials
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('credentials_sent_at')->nullable();
        });

        // One active reset code per user. Only the hash of the code is stored.
        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->id();
            // unique = one row per user; deleting the user deletes the code (cascade)
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code_hash');                          // hashed code, never the real code
            $table->timestamp('expires_at');                      // the code stops working after this time
            $table->unsignedTinyInteger('attempts')->default(0);  // wrong tries so far
            $table->timestamps();
        });
    }

    // Undo: drop the table first, then the column
    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credentials_sent_at');
        });
    }
};

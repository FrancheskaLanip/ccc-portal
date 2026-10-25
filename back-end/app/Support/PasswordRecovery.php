<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Forgot password rules:
 *  - First time: the user is emailed their login credentials (username + password).
 *  - Every time after that: the user is emailed a 6-digit code, which is used to set a new password.
 */
class PasswordRecovery
{
    public const CODE_MINUTES = 15; // the code is valid for 15 minutes
    public const MAX_ATTEMPTS = 5;  // after 5 wrong tries the code stops working

    // Step 1: the user typed their email on the Forgot password page
    public static function request(string $email): void
    {
        // Find the account (with its student record, if any)
        $user = Rls::asSystem(fn () => User::with('student')->where('email', $email)->first());

        if (! $user) {
            return; // same response either way, so nobody can test which emails exist
        }

        // credentials_sent_at is empty = the credentials were never emailed before
        if ($user->credentials_sent_at === null) {
            // Students use their student number as username, others use their email
            $username = $user->role === 'student' && $user->student
                ? $user->student->student_number
                : $user->email;

            $password = Credentials::generatePassword();

            // Send first: if the email fails, nothing is saved and the user can try again
            Credentials::send($user, $username, $password);

            // Save the new password (hashed) and remember that the credentials were sent
            $user->password = Hash::make($password);
            $user->credentials_sent_at = now();
            $user->save();

            return;
        }

        // Not the first time: make a random 6-digit code and email it
        $code = (string) random_int(100000, 999999);

        Credentials::sendCode($user, $code, self::CODE_MINUTES);

        // Keep only the HASH of the code in the database, one row per user
        DB::table('password_reset_codes')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::CODE_MINUTES),
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    // Step 2: the user typed the code and a new password. Returns true when the password was changed.
    public static function reset(string $email, string $code, string $newPassword): bool
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return false;
        }

        $row = DB::table('password_reset_codes')->where('user_id', $user->id)->first();

        // No code, expired code, or too many wrong tries -> fail
        if (! $row || now()->gt($row->expires_at) || $row->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        // Compare the typed code with the stored hash
        if (! Hash::check($code, $row->code_hash)) {
            // Wrong code: count the attempt
            DB::table('password_reset_codes')->where('user_id', $user->id)->increment('attempts');

            return false;
        }

        // Correct code: set the new password
        $user->password = Hash::make($newPassword);
        $user->save();

        // The code can be used only once, so delete it
        DB::table('password_reset_codes')->where('user_id', $user->id)->delete();

        return true;
    }
}

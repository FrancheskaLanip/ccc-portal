<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

// HELPER: creates passwords and sends the account emails.
class Credentials
{
    // Random 10-character password
    public static function generatePassword(): string
    {
        return Str::random(10);
    }

    // Email the login credentials (username + password)
    public static function send(User $user, string $username, string $password): void
    {
        $body = "Hi {$user->name}, below is your new login credentials:\n\n"
            . "Username: {$username}\n"
            . "Password: {$password}\n\n"
            . "- CCC Portal";

        // Mail::raw sends a plain-text email (in development it shows up in Mailpit)
        Mail::raw($body, function ($message) use ($user) {
            $message->to($user->email)->subject('CCC Portal Login Credentials');
        });
    }

    // Email the 6-digit reset code
    public static function sendCode(User $user, string $code, int $minutes): void
    {
        $body = "Hi {$user->name}, use this code to set a new password:\n\n"
            . "Code: {$code}\n\n"
            . "The code expires in {$minutes} minutes. If you did not ask for it, ignore this email.\n\n"
            . "- CCC Portal";

        Mail::raw($body, function ($message) use ($user) {
            $message->to($user->email)->subject('CCC Portal Password Reset Code');
        });
    }
}

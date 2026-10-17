<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Credentials
{
    public static function generatePassword(): string
    {
        return Str::random(10);
    }

    public static function send(User $user, string $username, string $password): void
    {
        $body = "Hi {$user->name}, below is your new login credentials:\n\n"
            . "Username: {$username}\n"
            . "Password: {$password}\n\n"
            . "- CCC Portal";

        Mail::raw($body, function ($message) use ($user) {
            $message->to($user->email)->subject('CCC Portal Login Credentials');
        });
    }
}
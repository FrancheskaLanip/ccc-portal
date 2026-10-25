<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// MODEL: one row of the "users" table (the login account).
// Fillable = columns allowed in mass assignment (role is NOT here, so it must be set on purpose)
#[Fillable(['name', 'email', 'password'])]
// Hidden = never shown when the user is turned into JSON
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'credentials_sent_at' => 'datetime',   // when the first credentials email was sent
            'password' => 'hashed',                // password is hashed automatically when saved
        ];
    }

    // A student account has one student record (users.id -> students.user_id)
    public function student()
    {
        return $this->hasOne(Student::class);
    }
}

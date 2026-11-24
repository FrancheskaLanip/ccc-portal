<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// MODEL: one row of "announcements".
class Announcement extends Model
{
    protected $fillable = ['title', 'body', 'audience', 'posted_by'];

    // The staff member who posted it
    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
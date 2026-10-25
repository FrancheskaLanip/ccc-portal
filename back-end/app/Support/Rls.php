<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

// ROW-LEVEL SECURITY helper.
// Before login, nobody is identified yet (for example: finding a user by student number).
// Those few lookups run in a "system" context, which the RLS policy allows to READ students only.
class Rls
{
    public static function asSystem(callable $callback)
    {
        // Remember the current role so we can put it back afterwards
        $previous = DB::selectOne("select current_setting('app.role', true) as role")->role ?? '';

        DB::select("select set_config('app.role', 'system', false)");

        try {
            return $callback();
        } finally {
            // Always restore, even if the callback throws an error
            DB::select("select set_config('app.role', ?, false)", [$previous]);
        }
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

// ROW-LEVEL SECURITY (RLS): this middleware runs on every request.
// It tells PostgreSQL who is logged in, so the policies on "students" can decide which rows that person may see.
class SetDatabaseUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user(); // the logged-in user, or null for a guest

        // Save the user's id and role as settings on this database connection.
        // A guest gets empty values, so a guest sees no student rows.
        DB::select(
            "select set_config('app.user_id', ?, false), set_config('app.role', ?, false)",
            [(string) ($user?->id ?? ''), (string) ($user?->role ?? '')]
        );

        return $next($request);
    }
}
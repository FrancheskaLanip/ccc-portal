<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// MIDDLEWARE: runs BEFORE the controller. Here it checks the user's role.
class RoleMiddleware
{
    /**
     * Allow the request only if the logged-in user's role is in the list.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // $roles = the list written in the route, e.g. role:admin,registrar
        // Not logged in, or role not in the list -> 403 Forbidden
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            abort(403);
        }

        // Role is allowed, so continue to the controller
        return $next($request);
    }
}

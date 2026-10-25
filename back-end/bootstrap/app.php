<?php

// APP SETUP: tells Laravel where the routes are and registers our middleware.

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',          // all our routes live in web.php
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',                                 // simple "is the app alive" URL
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Short name "role" so routes can write ->middleware('role:admin')
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // For /api requests, errors are returned as JSON (not an HTML error page)
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

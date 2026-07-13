<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Feature Flag: Replace 'verified' middleware with feature flag-aware version
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            // Feature Flag: Email Verification
            // This middleware respects FEATURE_EMAIL_VERIFICATION config
            'verified' => \App\Http\Middleware\VerifyEmailFeatureFlag::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

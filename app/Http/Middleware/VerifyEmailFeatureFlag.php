<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verify Email Feature Flag Middleware
 *
 * This middleware conditionally enforces email verification based on feature flags.
 *
 * Development Mode (FEATURE_EMAIL_VERIFICATION=false):
 * - Allows access even if email is not verified
 * - Useful for local development and testing
 *
 * Production Mode (FEATURE_EMAIL_VERIFICATION=true):
 * - Enforces email verification before accessing protected routes
 * - Redirects unverified users to verification notice
 */
class VerifyEmailFeatureFlag
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Feature Flag: Email Verification
        // If email verification is disabled, allow access regardless of verification status
        if (! config('features.email_verification')) {
            return $next($request);
        }

        // If email verification is enabled, apply Laravel's default verified check
        $user = $request->user();

        if ($user && $user->email_verified_at === null) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}

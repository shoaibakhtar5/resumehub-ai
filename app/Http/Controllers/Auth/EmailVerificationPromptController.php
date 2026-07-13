<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     *
     * Feature Flag: Email Verification
     * If email verification is disabled, redirect to dashboard.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        // Feature Flag: Email Verification
        // If email verification feature is disabled, skip verification
        if (! config('features.email_verification')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}

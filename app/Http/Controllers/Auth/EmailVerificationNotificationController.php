<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * Feature Flag: Email Verification
     * This controller respects the email_verification feature flag.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Feature Flag: Email Verification
        // Only send verification email if feature is enabled
        if (config('features.email_verification')) {
            $request->user()->sendEmailVerificationNotification();

            return back()->with('status', 'verification-link-sent');
        }

        // Feature Flag: Development Mode
        // In development mode (feature disabled), auto-verify the email
        $request->user()->forceFill(['email_verified_at' => now()])->save();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}

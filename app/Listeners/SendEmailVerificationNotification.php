<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Send Email Verification Notification Listener
 *
 * This listener conditionally sends email verification notifications based on feature flags.
 *
 * When FEATURE_EMAIL_VERIFICATION is disabled:
 * - Does not send verification emails
 * - Prevents unnecessary mail queue jobs
 * - Allows development without SMTP configuration
 *
 * When FEATURE_EMAIL_VERIFICATION is enabled:
 * - Sends verification emails as per Laravel's default behavior
 */
class SendEmailVerificationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * Feature Flag: Email Verification
     * This listener respects the email_verification feature flag.
     */
    public function handle(Registered $event): void
    {
        // Feature Flag: Email Verification
        // If email verification is disabled in development, skip sending the email
        if (! config('features.email_verification')) {
            return;
        }

        // Feature Flag: Email Verification (Production)
        // Only send verification email if feature is enabled
        $event->user->sendEmailVerificationNotification();
    }
}

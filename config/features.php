<?php

/**
 * Feature Flags Configuration
 *
 * This configuration file manages all feature flags for the application.
 * Feature flags can be enabled/disabled from .env without changing any code.
 *
 * Usage:
 *   config('features.email_verification')   // Returns true/false
 *   config('features.ai')                    // Returns true/false
 *
 * Environment-Based Behavior:
 * - Development (APP_ENV=local): Most features disabled by default for testing
 * - Production (APP_ENV=production): All features enabled by default
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    |
    | When disabled (false):
    | - Email verification is skipped after registration
    | - Users are automatically logged in
    | - No verification emails are sent
    | - Users can log in without email verification
    | - Dashboard is immediately accessible
    |
    | When enabled (true):
    | - Verification emails are sent after registration
    | - Users must verify email before accessing protected routes
    | - Unverified users see the verification notice
    |
    | Default: Based on APP_ENV (false for local, true for production)
    */
    'email_verification' => env('FEATURE_EMAIL_VERIFICATION', env('APP_ENV') !== 'local'),

    /*
    |--------------------------------------------------------------------------
    | AI Features
    |--------------------------------------------------------------------------
    |
    | Enable/disable AI-powered features:
    | - Resume review
    | - Resume scoring
    | - Cover letter generator
    | - Interview question generator
    | - Keyword optimizer
    */
    'ai' => env('FEATURE_AI', true),

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    |
    | Enable/disable payment processing and premium features.
    */
    'payments' => env('FEATURE_PAYMENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Blog
    |--------------------------------------------------------------------------
    |
    | Enable/disable blog functionality.
    */
    'blog' => env('FEATURE_BLOG', true),

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    |
    | Enable/disable analytics tracking.
    */
    'analytics' => env('FEATURE_ANALYTICS', true),

    /*
    |--------------------------------------------------------------------------
    | Social Login
    |--------------------------------------------------------------------------
    |
    | Enable/disable social authentication (Google, LinkedIn, etc).
    */
    'social_login' => env('FEATURE_SOCIAL_LOGIN', true),

    /*
    |--------------------------------------------------------------------------
    | OTP Login
    |--------------------------------------------------------------------------
    |
    | Enable/disable one-time password authentication.
    */
    'otp_login' => env('FEATURE_OTP_LOGIN', true),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Enable/disable notification system.
    */
    'notifications' => env('FEATURE_NOTIFICATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | ATS Checker
    |--------------------------------------------------------------------------
    |
    | Enable/disable ATS (Applicant Tracking System) checker functionality.
    */
    'ats_checker' => env('FEATURE_ATS_CHECKER', true),

    /*
    |--------------------------------------------------------------------------
    | Resume Templates
    |--------------------------------------------------------------------------
    |
    | Enable/disable resume template functionality.
    */
    'resume_templates' => env('FEATURE_RESUME_TEMPLATES', true),

    /*
    |--------------------------------------------------------------------------
    | Helper Method
    |--------------------------------------------------------------------------
    */
];

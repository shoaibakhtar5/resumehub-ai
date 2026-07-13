<?php

namespace App\Providers;

use App\Listeners\SendEmailVerificationNotification;
use App\Livewire\LiveResumePreview;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Feature Flag: Email Verification
        // Register the custom event listener that respects feature flags
        // This listener conditionally sends verification emails based on FEATURE_EMAIL_VERIFICATION
        $this->app['events']->listen(
            Registered::class,
            SendEmailVerificationNotification::class
        );

        // Register Livewire components
        Livewire::component('live-resume-preview', LiveResumePreview::class);
    }
}

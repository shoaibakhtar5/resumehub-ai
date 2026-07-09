<x-guest-layout title="Log in" eyebrow="Welcome back" heading="Continue building with precision AI.">
    <div class="mb-7">
        <x-ui.badge variant="ai" icon="sparkles">Secure workspace</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Log in to ResumeHub AI</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">Access your resumes, ATS checks, shared links, and AI writing history.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="grid gap-3 sm:grid-cols-2">
        <a href="{{ route('social.redirect', 'google') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-border-light bg-white px-4 py-3 font-display text-label-md text-on-surface shadow-soft transition hover:border-primary/30 hover:text-primary rh-focus">
            <x-ui.icon name="globe-alt" class="h-5 w-5" />
            Google
        </a>
        <a href="{{ route('social.redirect', 'linkedin-openid') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-border-light bg-white px-4 py-3 font-display text-label-md text-on-surface shadow-soft transition hover:border-primary/30 hover:text-primary rh-focus">
            <x-ui.icon name="briefcase" class="h-5 w-5" />
            LinkedIn
        </a>
    </div>

    <div class="my-6 flex items-center gap-3">
        <span class="h-px flex-1 bg-border-light"></span>
        <span class="text-label-sm uppercase text-on-surface-variant">or email</span>
        <span class="h-px flex-1 bg-border-light"></span>
    </div>

    <form method="POST" action="{{ route('login') }}" class="grid gap-5">
        @csrf
        <x-ui.input label="Email address" id="email" name="email" type="email" :value="old('email')" required autofocus autocomplete="username" :error="$errors->first('email')" />
        <x-ui.input label="Password" id="password" name="password" type="password" required autocomplete="current-password" :error="$errors->first('password')" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 text-body-sm text-on-surface-variant">
                <input id="remember_me" type="checkbox" class="rounded border-border-light text-primary focus:ring-primary" name="remember">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-display text-label-md text-primary rh-focus">Forgot password?</a>
            @endif
        </div>

        <x-ui.button type="submit" iconAfter="arrow-right" class="w-full">Log in</x-ui.button>
    </form>

    <form method="POST" action="{{ route('otp.send') }}" class="mt-4">
        @csrf
        <input type="hidden" name="email" value="{{ old('email') }}">
        <button type="submit" class="w-full font-display text-label-md text-primary rh-focus">Email me a one-time login code</button>
    </form>

    <p class="mt-6 text-center text-body-sm text-on-surface-variant">
        New to ResumeHub AI?
        <a href="{{ route('register') }}" class="font-display text-primary rh-focus">Create an account</a>
    </p>
</x-guest-layout>

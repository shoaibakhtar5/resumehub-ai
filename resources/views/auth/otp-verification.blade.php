<x-guest-layout title="OTP verification" eyebrow="Verification" heading="Enter the code and keep moving.">
    <div class="mb-7">
        <x-ui.badge variant="ai" icon="shield-check">OTP verification</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Enter your 6-digit code</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">Use the one-time password sent to your email or authenticator app.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('otp.send') }}" class="mb-6 grid gap-4">
        @csrf
        <x-ui.input label="Email address" name="email" type="email" :value="old('email', session('otp_email'))" required autocomplete="email" :error="$errors->first('email')" />
        <x-ui.button type="submit" variant="secondary" icon="envelope">Send code</x-ui.button>
    </form>

    <form method="POST" action="{{ route('otp.verify') }}" class="grid gap-5">
        @csrf
        <input type="hidden" name="email" value="{{ old('email', session('otp_email')) }}">
        <div class="grid grid-cols-6 gap-2" aria-label="One-time password">
            @for ($i = 1; $i <= 6; $i++)
                <input type="text" name="otp[]" inputmode="numeric" maxlength="1" aria-label="Digit {{ $i }}" class="h-14 rounded-md border border-border-light bg-white text-center font-display text-xl font-bold text-on-surface shadow-soft focus:border-primary focus:ring-primary/20">
            @endfor
        </div>
        <x-input-error :messages="$errors->get('otp')" />
        <x-ui.button type="submit" iconAfter="arrow-right" class="w-full">Verify code</x-ui.button>
    </form>

    <p class="mt-6 text-center text-body-sm text-on-surface-variant">
        Need a new code?
        <button formmethod="POST" form="otp-resend" type="submit" class="font-display text-primary rh-focus">Resend OTP</button>
    </p>
    <form id="otp-resend" method="POST" action="{{ route('otp.send') }}" class="hidden">
        @csrf
        <input type="hidden" name="email" value="{{ old('email', session('otp_email')) }}">
    </form>
</x-guest-layout>

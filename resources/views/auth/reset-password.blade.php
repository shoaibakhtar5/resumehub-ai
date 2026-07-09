<x-guest-layout title="Reset password" eyebrow="Secure reset" heading="Set a stronger password.">
    <div class="mb-7">
        <x-ui.badge variant="soft" icon="lock-closed">Protected flow</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Reset your password</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">Choose a new password to regain access to your resumes and exports.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="grid gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-ui.input label="Email address" id="email" name="email" type="email" :value="old('email', $request->email)" required autofocus autocomplete="username" :error="$errors->first('email')" />
        <x-ui.input label="New password" id="password" name="password" type="password" required autocomplete="new-password" :error="$errors->first('password')" />
        <x-ui.input label="Confirm password" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" :error="$errors->first('password_confirmation')" />
        <x-ui.button type="submit" iconAfter="arrow-right" class="w-full">Reset password</x-ui.button>
    </form>
</x-guest-layout>

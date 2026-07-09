<x-guest-layout title="Confirm password" eyebrow="Secure area" heading="Confirm before changing sensitive settings.">
    <div class="mb-7">
        <x-ui.badge variant="soft" icon="shield-check">Protected action</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Confirm your password</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">This keeps profile, billing, and account security changes protected.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="grid gap-5">
        @csrf
        <x-ui.input label="Password" id="password" name="password" type="password" required autocomplete="current-password" :error="$errors->first('password')" />
        <x-ui.button type="submit" icon="shield-check" class="w-full">Confirm</x-ui.button>
    </form>
</x-guest-layout>

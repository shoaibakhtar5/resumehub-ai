<x-guest-layout title="Two-factor authentication" eyebrow="Two-factor" heading="A safer gate for your career workspace.">
    <div class="mb-7">
        <x-ui.badge variant="soft" icon="lock-closed">Two-factor authentication</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Verify your identity</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">Enter your authenticator code or use a recovery code to continue.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="grid gap-5">
        @csrf
        <x-ui.input label="Authenticator code" name="code" type="text" inputmode="numeric" placeholder="123456" required />
        <x-ui.input label="Recovery code" name="recovery_code" type="text" placeholder="Optional recovery code" />
        <x-ui.button type="submit" icon="shield-check" class="w-full">Continue securely</x-ui.button>
    </form>
</x-guest-layout>

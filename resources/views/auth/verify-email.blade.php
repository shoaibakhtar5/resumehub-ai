<x-guest-layout title="Verify email" eyebrow="Verify email" heading="One quick check before the studio opens.">
    <div class="mb-7">
        <x-ui.badge variant="soft" icon="envelope">Email verification</x-ui.badge>
        <h1 class="mt-5 font-display text-headline-lg text-on-surface">Check your inbox</h1>
        <p class="mt-2 text-body-md text-on-surface-variant">We sent a verification link to your email address. Verify it to unlock ResumeHub AI.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-body-sm text-emerald-700">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit" class="w-full" icon="envelope">Resend email</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="secondary" class="w-full">Log out</x-ui.button>
        </form>
    </div>
</x-guest-layout>

<x-app-layout title="Profile" mode="user">
    <div class="space-y-8">
        <x-ui.page-header eyebrow="Account" title="Profile" description="Manage identity, password, verification, and account deletion from a focused settings surface.">
            <x-ui.button href="{{ route('settings') }}" variant="secondary" icon="cog-6-tooth">Settings</x-ui.button>
        </x-ui.page-header>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-6">
                <x-ui.card>
                    @include('profile.partials.update-profile-information-form')
                </x-ui.card>
                <x-ui.card>
                    @include('profile.partials.update-password-form')
                </x-ui.card>
                <x-ui.card>
                    @include('profile.partials.delete-user-form')
                </x-ui.card>
            </div>

            <aside class="space-y-5">
                <x-ui.card>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary text-white">
                            <span class="font-display text-2xl font-bold">{{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}</span>
                        </span>
                        <div>
                            <h2 class="font-display text-headline-md text-on-surface">{{ $user->name }}</h2>
                            <p class="text-body-sm text-on-surface-variant">{{ $user->email }}</p>
                        </div>
                    </div>
                </x-ui.card>
                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Workspace health</h2>
                    <div class="mt-5 space-y-4">
                        <x-ui.progress label="Profile completeness" value="90" />
                        <x-ui.progress label="Security setup" value="80" />
                        <x-ui.progress label="Export readiness" value="94" />
                    </div>
                </x-ui.card>
            </aside>
        </div>
    </div>
</x-app-layout>

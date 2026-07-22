<div class="space-y-6">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#111c3a] via-[#283b78] to-[#5b3de2] px-6 py-7 text-white shadow-xl shadow-indigo-950/10 sm:px-8">
        <div class="absolute -right-12 -top-16 h-52 w-52 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-5">
                <x-ui.avatar :user="$user" size="h-20 w-20" text-size="text-2xl" class="ring-4 ring-white/20" />
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-200">Account profile</p>
                    <h1 class="mt-1 truncate font-display text-2xl font-bold sm:text-3xl">{{ $user->name }}</h1>
                    <p class="mt-1 truncate text-sm text-indigo-100">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold">{{ $user->hasVerifiedEmail() ? 'Verified email' : 'Verification pending' }}</span>
                <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold">{{ $user->hasRole('admin') ? 'Administrator' : 'Career workspace' }}</span>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @include('profile.partials.update-profile-information-form')
            </section>
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @include('profile.partials.update-password-form')
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><x-ui.icon name="shield-check" class="h-5 w-5" /></span>
                    <div><h2 class="font-display font-semibold text-slate-900">Account security</h2><p class="text-xs text-slate-500">Keep access protected</p></div>
                </div>
                <dl class="mt-5 divide-y divide-slate-100 text-sm">
                    <div class="flex items-center justify-between py-3"><dt class="text-slate-500">Email status</dt><dd class="font-semibold {{ $user->hasVerifiedEmail() ? 'text-emerald-600' : 'text-amber-600' }}">{{ $user->hasVerifiedEmail() ? 'Verified' : 'Pending' }}</dd></div>
                    <div class="flex items-center justify-between py-3"><dt class="text-slate-500">Timezone</dt><dd class="font-semibold text-slate-800">{{ $user->timezone ?: 'UTC' }}</dd></div>
                    <div class="flex items-center justify-between py-3"><dt class="text-slate-500">Language</dt><dd class="font-semibold uppercase text-slate-800">{{ $user->locale ?: 'en' }}</dd></div>
                    <div class="flex items-center justify-between py-3"><dt class="text-slate-500">Member since</dt><dd class="font-semibold text-slate-800">{{ $user->created_at?->format('M Y') }}</dd></div>
                </dl>
                @unless($user->hasRole('admin'))
                    <a href="{{ route('settings') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"><x-ui.icon name="cog-6-tooth" class="h-4 w-4" /> Workspace settings</a>
                @endunless
            </section>

            <section class="overflow-hidden rounded-2xl border border-rose-200 bg-rose-50/50 shadow-sm">
                @include('profile.partials.delete-user-form')
            </section>
        </aside>
    </div>
</div>

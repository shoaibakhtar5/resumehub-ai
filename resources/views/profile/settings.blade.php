<x-app-layout title="Settings" mode="user">
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-r from-white via-indigo-50/70 to-violet-50 p-6 shadow-sm sm:p-8">
            <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-violet-200/30 blur-3xl"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-600">Workspace preferences</p><h1 class="mt-2 font-display text-3xl font-bold text-slate-950">Settings</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Configure regional preferences and review the security controls connected to your ResumeHub AI account.</p></div>
                <x-ui.button href="{{ route('profile.edit') }}" variant="white" icon="user">Open profile</x-ui.button>
            </div>
        </section>

        @if (session('status') === 'settings-updated')
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Settings saved successfully.</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <a href="{{ route('settings') }}" class="flex items-center gap-3 rounded-xl bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700"><x-ui.icon name="cog-6-tooth" class="h-5 w-5" /> Preferences</a>
                <a href="{{ route('profile.edit') }}" class="mt-1 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-indigo-700"><x-ui.icon name="user" class="h-5 w-5" /> Profile details</a>
                <a href="{{ route('profile.edit') }}#security" class="mt-1 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-indigo-700"><x-ui.icon name="lock-closed" class="h-5 w-5" /> Password & security</a>
            </aside>

            <div class="space-y-6">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-7"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><x-ui.icon name="globe-alt" class="h-5 w-5" /></span><div><h2 class="font-display text-lg font-semibold text-slate-900">Language and region</h2><p class="text-sm text-slate-500">Used for dates, timestamps, and account communication.</p></div></div></div>
                    <form method="POST" action="{{ route('settings.update') }}" class="grid gap-5 p-6 sm:grid-cols-2 sm:p-7">
                        @csrf
                        @method('PATCH')
                        <x-ui.select label="Display language" name="locale" :selected="old('locale', $user->locale ?: 'en')" :options="['en' => 'English', 'en-GB' => 'English (UK)', 'ur' => 'Urdu']" />
                        <div>
                            <x-ui.input label="Timezone" name="timezone" type="text" list="settings-timezones" :value="old('timezone', $user->timezone ?: 'UTC')" required :error="$errors->first('timezone')" />
                            <datalist id="settings-timezones">@foreach(['UTC','Asia/Karachi','Asia/Dubai','Asia/Kolkata','Europe/London','Europe/Berlin','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','Australia/Sydney'] as $timezone)<option value="{{ $timezone }}"></option>@endforeach</datalist>
                        </div>
                        <div class="border-t border-slate-100 pt-6 sm:col-span-2"><x-ui.button type="submit" icon="check">Save preferences</x-ui.button></div>
                    </form>
                </section>

                <section class="grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('profile.edit') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><x-ui.icon name="shield-check" class="h-5 w-5" /></span><h3 class="mt-4 font-display font-semibold text-slate-900">Security controls</h3><p class="mt-2 text-sm leading-6 text-slate-500">Change your password and review email verification.</p><span class="mt-4 inline-flex text-sm font-semibold text-indigo-600">Manage security →</span></a>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><x-ui.icon name="check" class="h-5 w-5" /></span><h3 class="mt-4 font-display font-semibold text-slate-900">Account status</h3><p class="mt-2 text-sm leading-6 text-slate-500">Your account is {{ $user->status }} and {{ $user->hasVerifiedEmail() ? 'email verified' : 'awaiting email verification' }}.</p><span class="mt-4 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ Str::headline($user->status) }}</span></div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>

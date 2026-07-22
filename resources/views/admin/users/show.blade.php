<x-admin-layout title="User Profile">
    @include('admin.access._tabs')
    <div class="mx-auto max-w-5xl space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.users') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600">← Back to Users</a>
            <div class="flex gap-2">
                <a href="{{ route('admin.users.edit', $managedUser) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"><x-ui.icon name="pencil-square" class="h-4 w-4" /> Edit User</a>
                @can('delete', $managedUser)<form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Delete</button></form>@endcan
            </div>
        </div>

        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0d1e3d] via-[#243b78] to-[#5a3de2] p-6 text-white shadow-xl">
            <div class="absolute -right-10 -top-20 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                <x-ui.avatar :user="$managedUser" size="h-24 w-24" text-size="text-3xl" class="ring-4 ring-white/20" />
                <div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-3"><h2 class="font-display text-2xl font-bold">{{ $managedUser->name }}</h2><span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ Str::headline($managedUser->status) }}</span></div><p class="mt-1 text-sm text-indigo-100">{{ $managedUser->email }}</p><p class="mt-1 text-sm text-indigo-100">{{ $managedUser->phone ?: 'No phone number' }}</p></div>
                <div class="rounded-xl border border-white/15 bg-white/10 px-6 py-4 text-center"><p class="font-display text-3xl font-bold">{{ $managedUser->resumes_count }}</p><p class="text-xs text-indigo-100">Resumes</p></div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="font-display font-semibold">Assigned Roles</h3><div class="mt-4 space-y-3">@forelse($managedUser->roles as $role)<a href="{{ route('admin.roles.show', $role) }}" class="flex items-center justify-between rounded-xl border border-slate-200 p-3 transition hover:border-indigo-200 hover:bg-indigo-50/40"><span><span class="block text-sm font-semibold">{{ Str::headline($role->name) }}</span><span class="text-xs text-slate-500">{{ $role->description ?: 'No description' }}</span></span><span class="text-xs font-semibold text-indigo-600">{{ $role->permissions->count() }} permissions</span></a>@empty<p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No roles assigned.</p>@endforelse</div></section>
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="font-display font-semibold">Account Information</h3><dl class="mt-4 divide-y divide-slate-100 text-sm"><div class="flex justify-between py-3"><dt class="text-slate-500">Joined</dt><dd class="font-medium">{{ $managedUser->created_at->format('M j, Y') }}</dd></div><div class="flex justify-between py-3"><dt class="text-slate-500">Last login</dt><dd class="font-medium">{{ $managedUser->last_login_at?->diffForHumans() ?? 'Never' }}</dd></div><div class="flex justify-between py-3"><dt class="text-slate-500">Email verified</dt><dd class="font-medium">{{ $managedUser->email_verified_at ? 'Yes' : 'No' }}</dd></div><div class="flex justify-between py-3"><dt class="text-slate-500">Timezone</dt><dd class="font-medium">{{ $managedUser->timezone ?: 'UTC' }}</dd></div><div class="flex justify-between py-3"><dt class="text-slate-500">Language</dt><dd class="font-medium uppercase">{{ $managedUser->locale ?: 'en' }}</dd></div><div class="flex justify-between py-3"><dt class="text-slate-500">Administrator</dt><dd class="font-medium">{{ $managedUser->is_admin ? 'Yes' : 'No' }}</dd></div></dl></section>
        </div>
    </div>
</x-admin-layout>

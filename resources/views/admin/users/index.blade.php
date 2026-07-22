<x-admin-layout title="Users">
    @include('admin.access._tabs')

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><h2 class="font-display text-xl font-bold">User Management</h2><p class="mt-1 text-sm text-slate-500">Manage accounts, status, profile images, and role access.</p></div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"><x-ui.icon name="plus" class="h-4 w-4" /> Add User</a>
    </div>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-[minmax(220px,1fr)_180px_200px_auto]" x-data="{ loading: false }" @submit="loading = true">
            <label class="flex items-center rounded-lg border border-slate-200 px-3 focus-within:border-indigo-400 focus-within:ring-2 focus-within:ring-indigo-100"><x-ui.icon name="magnifying-glass" class="h-4 w-4 text-slate-400" /><input name="search" value="{{ request('search') }}" class="w-full border-0 bg-transparent px-2 py-2 text-sm focus:ring-0" placeholder="Search name, email, or phone..."></label>
            <select name="status" class="rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
            <select name="role_id" class="rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200"><option value="">All roles</option>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected((int) request('role_id') === $role->id)>{{ Str::headline($role->name) }}</option>@endforeach</select>
            <button :disabled="loading" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold hover:bg-slate-50 disabled:opacity-60" x-text="loading ? 'Loading...' : 'Apply'">Apply</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500"><tr>
                    @foreach (['name' => 'User', 'email' => 'Email', 'status' => 'Status'] as $sort => $label)
                        <th class="px-4 py-3"><a class="inline-flex items-center gap-1 hover:text-indigo-600" href="{{ request()->fullUrlWithQuery(['sort' => $sort, 'direction' => request('sort') === $sort && request('direction') === 'asc' ? 'desc' : 'asc']) }}">{{ $label }} @if(request('sort') === $sort){{ request('direction') === 'asc' ? '↑' : '↓' }}@endif</a></th>
                    @endforeach
                    <th class="px-4 py-3">Roles</th><th class="px-4 py-3">Resumes</th><th class="px-4 py-3">Joined</th><th class="px-4 py-3 text-right">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($users as $managedUser)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-4 py-3"><div class="flex items-center gap-3">
                                <x-ui.avatar :user="$managedUser" size="h-10 w-10" text-size="text-sm" class="ring-2 ring-slate-100" />
                                <div><a href="{{ route('admin.users.show', $managedUser) }}" class="font-semibold text-slate-900 hover:text-indigo-600">{{ $managedUser->name }}</a><p class="text-xs text-slate-500">{{ $managedUser->phone ?: 'No phone' }}</p></div>
                            </div></td>
                            <td class="px-4 py-3 text-slate-600">{{ $managedUser->email }}</td>
                            <td class="px-4 py-3"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $managedUser->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}"><span class="h-1.5 w-1.5 rounded-full {{ $managedUser->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>{{ Str::headline($managedUser->status) }}</span></td>
                            <td class="px-4 py-3"><div class="flex flex-wrap gap-1">@forelse($managedUser->roles as $role)<span class="rounded-md bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-700">{{ Str::headline($role->name) }}</span>@empty<span class="text-xs text-slate-400">No role</span>@endforelse</div></td>
                            <td class="px-4 py-3 text-slate-600">{{ $managedUser->resumes_count }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $managedUser->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3"><div class="flex justify-end gap-1"><a href="{{ route('admin.users.show', $managedUser) }}" class="rounded-lg p-2 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600"><x-ui.icon name="eye" class="h-4 w-4" /></a><a href="{{ route('admin.users.edit', $managedUser) }}" class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600"><x-ui.icon name="pencil-square" class="h-4 w-4" /></a>@can('delete', $managedUser)<form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')<button class="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600"><x-ui.icon name="trash" class="h-4 w-4" /></button></form>@endcan</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500"><x-ui.icon name="users" /></span><p class="mt-3 font-semibold">No users found</p><p class="mt-1 text-sm text-slate-500">Adjust the filters or create a user.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())<div class="border-t border-slate-200 px-4 py-3">{{ $users->links() }}</div>@endif
    </section>
</x-admin-layout>

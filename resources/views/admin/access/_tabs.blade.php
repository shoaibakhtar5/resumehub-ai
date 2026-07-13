<nav class="mb-5 flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-white p-2 shadow-sm" aria-label="Users and access navigation">
    @foreach ([
        ['Users', 'admin.users', 'users'],
        ['Roles', 'admin.roles', 'shield-check'],
        ['Permissions', 'admin.permissions', 'key'],
    ] as [$label, $routeName, $icon])
        <a href="{{ route($routeName) }}" @class([
            'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition',
            'bg-indigo-600 text-white shadow-sm' => request()->routeIs($routeName, $routeName.'.*'),
            'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($routeName, $routeName.'.*'),
        ])>
            <x-ui.icon :name="$icon" class="h-4 w-4" /> {{ $label }}
        </a>
    @endforeach
</nav>

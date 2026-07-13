@php
    $groups = [
        null => [
            ['Dashboard', 'admin.dashboard', 'home'],
        ],
        'Management' => [
            ['Users', 'admin.users', 'users'],
            ['Roles & Permissions', 'admin.roles', 'shield-check'],
            ['Permissions', 'admin.permissions', 'key'],
            ['Templates', 'admin.templates', 'squares-2x2'],
            ['Resumes', 'admin.resumes', 'document-text'],
            ['Blog Posts', 'admin.blog', 'newspaper'],
            ['Pages', 'admin.pages', 'document-duplicate'],
            ['Team Members', 'admin.team', 'building-office'],
            ['AI Tools Usage', 'admin.ai-usage', 'sparkles'],
        ],
        'Business' => [
            ['Plans & Pricing', 'admin.plans', 'briefcase'],
            ['Subscriptions', 'admin.subscriptions', 'credit-card'],
            ['Transactions', 'admin.transactions', 'arrow-path'],
            ['Coupons', 'admin.coupons', 'tag'],
        ],
        'System' => [
            ['Settings', 'admin.settings', 'cog-6-tooth'],
            ['Email Templates', 'admin.email-templates', 'envelope'],
            ['Notifications', 'admin.notifications', 'bell'],
            ['Activity Logs', 'admin.logs', 'chart-bar'],
            ['System Status', 'admin.system-status', 'server-stack'],
        ],
    ];
@endphp

<aside class="fixed inset-y-0 left-0 z-50 flex w-[254px] -translate-x-full flex-col overflow-hidden bg-[linear-gradient(180deg,#0c1d3a_0%,#071a35_100%)] text-white shadow-2xl transition-all duration-200 lg:translate-x-0"
       :class="{ 'translate-x-0': sidebarOpen }" :style="collapsed ? 'width:82px' : ''">
    <div class="flex h-[88px] shrink-0 items-center justify-between px-5" :class="collapsed && 'lg:px-4'">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-300 to-white text-primary shadow-lg">
                <x-ui.icon name="sparkles" class="h-5 w-5" />
            </span>
            <span x-show="!collapsed" class="min-w-0 leading-tight">
                <span class="block truncate text-lg font-bold">ResumeHub AI</span>
                <span class="block text-xs text-slate-300">Admin Panel</span>
            </span>
        </a>
        <button type="button" class="hidden rounded-lg p-2 text-slate-300 hover:bg-white/10 hover:text-white lg:block" @click="collapsed = !collapsed" aria-label="Toggle sidebar">
            <x-ui.icon name="bars-3" class="h-5 w-5" />
        </button>
    </div>

    <nav class="admin-scrollbar flex-1 overflow-y-auto px-3 pb-4">
        @foreach ($groups as $group => $items)
            @if ($group)
                <p x-show="!collapsed" class="mb-2 mt-5 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $group }}</p>
            @endif
            <div class="space-y-1">
                @foreach ($items as [$label, $routeName, $icon])
                    <a href="{{ route($routeName) }}"
                       @class([
                           'flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] font-medium transition',
                           'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-950/25' => request()->routeIs($routeName, $routeName.'.*'),
                           'text-slate-200 hover:bg-white/10 hover:text-white' => ! request()->routeIs($routeName, $routeName.'.*'),
                       ])
                       :class="collapsed && 'lg:justify-center lg:px-2'"
                       title="{{ $label }}">
                        <x-ui.icon :name="$icon" class="h-[18px] w-[18px]" />
                        <span x-show="!collapsed">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="m-4 rounded-xl border border-white/15 bg-white/[0.04] p-3" :class="collapsed && 'lg:m-3 lg:p-2'">
        <div class="flex items-center gap-3" :class="collapsed && 'lg:justify-center'">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white font-bold text-[#102245]">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div x-show="!collapsed" class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-300">Super Admin</p>
            </div>
        </div>
    </div>
</aside>

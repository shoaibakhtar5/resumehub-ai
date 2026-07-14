<x-admin-layout title="Dashboard">
    <div class="mb-5 flex justify-end" x-data="{ open: false }">
        <div class="relative">
            <button type="button" @click="open = !open" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm">
                {{ $dateRange }}
                <x-ui.icon name="calendar" class="h-4 w-4 text-slate-500" />
            </button>
            <form x-cloak x-show="open" @click.outside="open = false" method="GET" class="absolute right-0 z-20 mt-2 grid w-72 gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
                <label class="text-xs font-semibold text-slate-600">From<input type="date" name="from" value="{{ $dateFilter['from'] }}" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200"></label>
                <label class="text-xs font-semibold text-slate-600">To<input type="date" name="to" value="{{ $dateFilter['to'] }}" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200"></label>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Apply date range</button>
            </form>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @php
            $toneClasses = [
                'violet' => ['bg-violet-100 text-violet-700', 'users'],
                'blue' => ['bg-blue-100 text-blue-700', 'document-text'],
                'emerald' => ['bg-emerald-100 text-emerald-700', 'squares-2x2'],
                'orange' => ['bg-orange-100 text-orange-600', 'heart'],
                'rose' => ['bg-rose-100 text-rose-600', 'credit-card'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <a href="{{ route('admin.'.$stat['route']) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $toneClasses[$stat['tone']][0] }}">
                        <x-ui.icon :name="$toneClasses[$stat['tone']][1]" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-1 font-display text-xl font-bold text-[#10182c]">
                            {{ $stat['currency'] ? '$'.number_format($stat['value'] / 100) : number_format($stat['value']) }}
                        </p>
                        <p @class(['mt-2 text-xs font-semibold', 'text-emerald-600' => $stat['change'] >= 0, 'text-rose-600' => $stat['change'] < 0])>
                            {{ $stat['change'] >= 0 ? '↑' : '↓' }} {{ abs($stat['change']) }}%
                        </p>
                        <p class="mt-1 text-[11px] text-slate-500">vs last 30 days</p>
                    </div>
                </div>
            </a>
        @endforeach
    </section>

    <section class="mt-5 grid gap-5 xl:grid-cols-12">
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-base font-semibold">Resume Creation Trend</h2>
                <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-600">Last 14 days</span>
            </div>
            <div id="admin-overview-chart" class="mt-3 min-h-[250px]" aria-label="Resume creation and user growth chart"></div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-3">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-base font-semibold">Recent Users</h2>
                <a href="{{ route('admin.users') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium hover:bg-slate-50">View All</a>
            </div>
            <div class="mt-4 space-y-3.5">
                @forelse ($recentUsers as $user)
                    <a href="{{ route('admin.users.show', $user) }}" class="flex items-center gap-3 rounded-lg p-1 hover:bg-slate-50">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700">{{ collect(explode(' ', $user->name))->map(fn ($part) => substr($part, 0, 1))->take(2)->join('') }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-semibold text-slate-800">{{ $user->name }}</span>
                            <span class="block truncate text-[11px] text-slate-500">{{ $user->email }}</span>
                        </span>
                        <span class="whitespace-nowrap text-[10px] text-slate-500">{{ $user->created_at->diffForHumans(short: true) }}</span>
                    </a>
                @empty
                    <p class="py-12 text-center text-sm text-slate-500">No users yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-4">
            <h2 class="font-display text-base font-semibold">System Overview</h2>
            <div class="mt-5 space-y-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><x-ui.icon name="server-stack" class="h-4 w-4" /></span>
                    <div class="min-w-0 flex-1"><p class="text-xs font-semibold">Server Status</p><p class="text-[11px] text-slate-500">All systems operational</p></div>
                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                </div>
                <div>
                    <div class="mb-2 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><x-ui.icon name="server-stack" class="h-4 w-4" /></span>
                        <div class="min-w-0 flex-1"><p class="text-xs font-semibold">Storage Usage</p><p class="text-[11px] text-slate-500">{{ $system['storageLabel'] }}</p></div>
                    </div>
                    <div class="ml-12 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-600" style="width: {{ $system['storagePercent'] }}%"></div></div>
                </div>
                <div>
                    <div class="mb-2 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><x-ui.icon name="sparkles" class="h-4 w-4" /></span>
                        <div class="min-w-0 flex-1"><p class="text-xs font-semibold">AI Requests (Today)</p><p class="text-[11px] text-slate-500">{{ number_format($system['aiToday']) }} / {{ number_format($system['aiLimit']) }}</p></div>
                    </div>
                    <div class="ml-12 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-violet-600" style="width: {{ min(100, ($system['aiToday'] / $system['aiLimit']) * 100) }}%"></div></div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-orange-600"><x-ui.icon name="server-stack" class="h-4 w-4" /></span>
                    <div class="min-w-0 flex-1"><p class="text-xs font-semibold">Database</p><p class="text-[11px] text-slate-500">{{ $system['database'] ? 'Connected and healthy' : 'Connection unavailable' }}</p></div>
                    <span class="h-3 w-3 rounded-full {{ $system['database'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                </div>
            </div>
        </article>
    </section>

    <section class="mt-5 grid gap-5 xl:grid-cols-12">
        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm xl:col-span-7">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h2 class="font-display text-base font-semibold">Recent Resumes</h2>
                <a href="{{ route('admin.resumes') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium hover:bg-slate-50">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Title</th><th class="px-4 py-3">Template</th><th class="px-4 py-3">Owner</th><th class="px-4 py-3">Updated</th><th class="px-4 py-3 text-right">Action</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($recentResumes as $resume)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $resume->title }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $resume->template?->name ?? 'No template' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $resume->user?->name ?? 'Deleted user' }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $resume->updated_at->diffForHumans(short: true) }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.resources.show', ['resource' => 'resumes', 'id' => $resume->id]) }}" class="inline-flex rounded-md p-1.5 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600"><x-ui.icon name="eye" class="h-4 w-4" /></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">No resumes have been created.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-5">
            <h2 class="font-display text-base font-semibold">Quick Actions</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['Add New Template', 'Upload a resume template', 'admin.templates.create', [], 'cloud-arrow-up', 'violet'],
                    ['Add New User', 'Create a new user account', 'admin.users.create', [], 'users', 'blue'],
                    ['Create Blog Post', 'Write a new blog post', 'admin.resources.create', ['resource' => 'blog'], 'pencil-square', 'violet'],
                    ['System Settings', 'Configure system settings', 'admin.settings', [], 'cog-6-tooth', 'orange'],
                    ['View All Users', 'Manage all users', 'admin.users', [], 'users', 'rose'],
                    ['Site Pages', 'Manage published content', 'admin.pages', [], 'globe-alt', 'emerald'],
                ] as [$label, $description, $route, $params, $icon, $tone])
                    @php
                        $quickTone = [
                            'violet' => 'bg-violet-50 text-violet-600',
                            'blue' => 'bg-blue-50 text-blue-600',
                            'orange' => 'bg-orange-50 text-orange-600',
                            'rose' => 'bg-rose-50 text-rose-600',
                            'emerald' => 'bg-emerald-50 text-emerald-600',
                        ][$tone];
                    @endphp
                    <a href="{{ route($route, $params) }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $quickTone }}"><x-ui.icon :name="$icon" class="h-5 w-5" /></span>
                        <span><span class="block text-xs font-semibold">{{ $label }}</span><span class="mt-0.5 block text-[10px] text-slate-500">{{ $description }}</span></span>
                    </a>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mt-5 grid gap-5 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between"><h2 class="font-display text-base font-semibold">Notifications</h2><a href="{{ route('admin.notifications') }}" class="text-xs font-semibold text-indigo-600">View all</a></div>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                    <div class="flex gap-3 py-3"><span class="mt-0.5 h-2 w-2 rounded-full {{ $notification->read_at ? 'bg-slate-300' : 'bg-indigo-500' }}"></span><div><p class="text-xs font-semibold">{{ data_get($notification->data, 'title', 'Notification') }}</p><p class="mt-1 text-xs text-slate-500">{{ data_get($notification->data, 'message') }}</p></div></div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">You’re all caught up.</p>
                @endforelse
            </div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between"><h2 class="font-display text-base font-semibold">Activity Summary</h2><a href="{{ route('admin.logs') }}" class="text-xs font-semibold text-indigo-600">View logs</a></div>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($activity as $event)
                    <div class="flex items-start gap-3 py-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600"><x-ui.icon name="chart-bar" class="h-4 w-4" /></span><div class="min-w-0 flex-1"><p class="truncate text-xs font-semibold">{{ Str::headline($event->event) }}</p><p class="truncate text-xs text-slate-500">{{ $event->description ?: 'Administrative activity recorded' }}</p></div><time class="text-[10px] text-slate-500">{{ $event->created_at->diffForHumans(short: true) }}</time></div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">No recent activity.</p>
                @endforelse
            </div>
        </article>
    </section>

    @push('scripts')
        <script>window.adminDashboardChart = @json($chart);</script>
    @endpush
</x-admin-layout>

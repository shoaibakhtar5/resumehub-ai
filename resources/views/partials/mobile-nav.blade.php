@php
    $items = $mode === 'admin'
        ? [
            ['label' => 'Admin', 'route' => 'admin.dashboard', 'icon' => 'chart-pie'],
            ['label' => 'Users', 'route' => 'admin.users', 'icon' => 'users'],
            ['label' => 'AI', 'route' => 'admin.ai-settings', 'icon' => 'sparkles'],
            ['label' => 'Logs', 'route' => 'admin.logs', 'icon' => 'server-stack'],
        ]
        : [
            ['label' => 'Studio', 'route' => 'dashboard', 'icon' => 'sparkles'],
            ['label' => 'Templates', 'route' => 'resume.templates', 'icon' => 'squares-2x2'],
            ['label' => 'Stats', 'route' => 'resume.score', 'icon' => 'chart-bar'],
            ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'user'],
        ];
@endphp

<nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-outline-variant/30 bg-surface/95 px-3 py-2 shadow-[0_-4px_18px_rgba(19,27,46,0.08)] backdrop-blur-xl safe-bottom lg:hidden" aria-label="Mobile dashboard navigation">
    <div class="grid grid-cols-4 gap-1">
        @foreach ($items as $item)
            @php($active = request()->routeIs($item['route']))
            <a href="{{ route($item['route']) }}" class="flex flex-col items-center justify-center rounded-lg px-2 py-2 text-center font-display text-label-sm transition {{ $active ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:text-primary' }}">
                <x-ui.icon :name="$item['icon']" class="h-5 w-5" />
                <span class="mt-1">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>

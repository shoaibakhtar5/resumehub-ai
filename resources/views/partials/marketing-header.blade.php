<header x-data="{ open: false }" class="fixed inset-x-0 top-0 z-50 border-b border-outline-variant/30 bg-surface/85 backdrop-blur-xl">
    <div class="rh-container flex h-16 items-center justify-between">
        <x-brand />
        <nav class="hidden items-center gap-7 md:flex" aria-label="Primary navigation">
            @foreach (config('resumehub.marketing_nav', []) as $item)
                <a href="{{ route($item['route']) }}" class="font-display text-label-md text-on-surface-variant transition hover:text-primary {{ request()->routeIs($item['route']) ? 'text-primary' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="hidden items-center gap-3 md:flex">
            @auth
                <x-ui.button href="{{ route('dashboard') }}" variant="secondary" size="sm">Dashboard</x-ui.button>
            @else
                <x-ui.button href="{{ route('login') }}" variant="ghost" size="sm">Log in</x-ui.button>
                <x-ui.button href="{{ route('register') }}" size="sm">Start free</x-ui.button>
            @endauth
        </div>
        <button type="button" class="rounded-md p-2 text-on-surface-variant rh-focus md:hidden" aria-label="Open menu" x-on:click="open = true">
            <x-ui.icon name="bars-3" class="h-6 w-6" />
        </button>
    </div>

    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-on-background/40 md:hidden" x-on:click="open = false"></div>
    <aside x-cloak class="fixed right-0 top-0 z-50 h-screen w-80 max-w-[85vw] translate-x-full border-l border-border-light bg-white p-5 shadow-panel transition md:hidden" x-bind:class="open ? 'translate-x-0' : 'translate-x-full'">
        <div class="flex items-center justify-between">
            <x-brand />
            <button type="button" class="rounded-md p-2 text-on-surface-variant rh-focus" aria-label="Close menu" x-on:click="open = false">
                <x-ui.icon name="x-mark" class="h-6 w-6" />
            </button>
        </div>
        <nav class="mt-8 grid gap-2" aria-label="Mobile navigation">
            @foreach (config('resumehub.marketing_nav', []) as $item)
                <a href="{{ route($item['route']) }}" class="rounded-lg px-4 py-3 font-display text-label-md text-on-surface-variant transition hover:bg-surface-container hover:text-primary">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="mt-8 grid gap-3">
            @auth
                <x-ui.button href="{{ route('dashboard') }}">Dashboard</x-ui.button>
            @else
                <x-ui.button href="{{ route('login') }}" variant="secondary">Log in</x-ui.button>
                <x-ui.button href="{{ route('register') }}">Start free</x-ui.button>
            @endauth
        </div>
    </aside>
</header>

@php
    $nav = config($mode === 'admin' ? 'resumehub.admin_nav' : 'resumehub.user_nav', []);
@endphp

<div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-on-background/40 lg:hidden" x-on:click="sidebarOpen = false"></div>

<aside class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-outline-variant/30 bg-surface/95 px-4 py-5 shadow-panel backdrop-blur-xl transition duration-300 lg:translate-x-0" x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="flex items-center justify-between px-2">
        <x-brand :href="$mode === 'admin' ? route('admin.dashboard') : route('dashboard')" />
        <button type="button" class="rounded-md p-2 text-on-surface-variant rh-focus lg:hidden" aria-label="Close sidebar" x-on:click="sidebarOpen = false">
            <x-ui.icon name="x-mark" class="h-6 w-6" />
        </button>
    </div>

    <div class="mt-8 flex-1 overflow-y-auto pr-1">
        @foreach ($nav as $section)
            <div class="mb-7">
                <p class="mb-3 px-3 font-display text-label-sm uppercase text-on-surface-variant/70">{{ $section['label'] }}</p>
                <nav class="grid gap-1" aria-label="{{ $section['label'] }}">
                    @foreach ($section['items'] as $item)
                        @php($active = request()->routeIs($item['route']))
                        <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 font-display text-label-md transition {{ $active ? 'bg-primary text-white shadow-ai' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                            <x-ui.icon :name="$item['icon']" class="h-5 w-5" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl border border-ai-accent/20 bg-ai-accent/10 p-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-ai-accent shadow-soft">
                <x-ui.icon name="sparkles" class="h-5 w-5" />
            </span>
            <div>
                <p class="font-display text-label-md text-on-surface">{{ $mode === 'admin' ? 'AI controls active' : 'AI Studio active' }}</p>
                <p class="text-body-sm text-on-surface-variant">{{ $mode === 'admin' ? 'Monitor credits and prompts.' : '98.4% match accuracy.' }}</p>
            </div>
        </div>
    </div>
</aside>

<header class="fixed inset-x-0 top-0 z-30 border-b border-outline-variant/30 bg-surface/85 backdrop-blur-xl lg:left-72">
    <div class="flex h-16 items-center justify-between gap-4 px-4 md:px-8">
        <div class="flex items-center gap-3">
            <button type="button" class="rounded-md p-2 text-on-surface-variant rh-focus lg:hidden" aria-label="Open sidebar" x-on:click="sidebarOpen = true">
                <x-ui.icon name="bars-3" class="h-6 w-6" />
            </button>
            <div class="hidden min-w-0 items-center rounded-full border border-border-light bg-white px-4 py-2 shadow-soft md:flex md:w-96">
                <x-ui.icon name="magnifying-glass" class="h-5 w-5 text-on-surface-variant" />
                <input type="search" aria-label="Search ResumeHub AI" placeholder="Search resumes, templates, users..." class="ml-3 w-full border-0 bg-transparent p-0 text-body-sm text-on-surface placeholder:text-on-surface-variant focus:ring-0">
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ $mode === 'admin' ? route('admin.analytics') : route('notifications') }}" class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container hover:text-primary rh-focus" aria-label="Notifications">
                <x-ui.icon name="bell" class="h-6 w-6" />
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-full bg-white py-1 pl-1 pr-3 shadow-soft rh-focus">
                <span class="inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-primary text-white">
                    <span class="font-display text-label-md">{{ strtoupper(substr(auth()->user()->name ?? 'Alex', 0, 1)) }}</span>
                </span>
                <span class="hidden text-left sm:block">
                    <span class="block font-display text-label-md text-on-surface">{{ auth()->user()->name ?? 'Alex Rivers' }}</span>
                    <span class="block text-label-sm text-on-surface-variant">{{ $mode === 'admin' ? 'Admin workspace' : 'Career workspace' }}</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container hover:text-primary rh-focus" aria-label="Log out" title="Log out">
                    <x-ui.icon name="arrow-left-on-rectangle" class="h-6 w-6" />
                </button>
            </form>
        </div>
    </div>
</header>

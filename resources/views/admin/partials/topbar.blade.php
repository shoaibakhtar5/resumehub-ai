<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
    <div class="flex min-h-[88px] items-center gap-4 px-4 sm:px-6 lg:px-7">
        <button type="button" class="rounded-lg border border-slate-200 p-2 text-slate-600 lg:hidden" @click="sidebarOpen = true" aria-label="Open navigation">
            <x-ui.icon name="bars-3" class="h-5 w-5" />
        </button>
        <div class="min-w-0 flex-1">
            <h1 class="truncate font-display text-2xl font-bold text-[#111a2f]">{{ $pageTitle }}</h1>
            <p class="hidden text-sm text-slate-500 sm:block">Welcome back! Here’s what’s happening with your ResumeHub AI platform.</p>
        </div>
        <form action="{{ route('admin.users') }}" method="GET" class="hidden w-full max-w-[290px] items-center rounded-lg border border-slate-200 bg-white px-3 py-2.5 shadow-sm md:flex">
            <x-ui.icon name="magnifying-glass" class="h-4 w-4 text-slate-500" />
            <input name="search" type="search" class="w-full border-0 bg-transparent px-2 py-0 text-sm focus:ring-0" placeholder="Search anything..." aria-label="Search admin records">
            <kbd class="whitespace-nowrap rounded border border-slate-200 px-1.5 py-0.5 text-[10px] text-slate-500">Ctrl + /</kbd>
        </form>
        <a href="{{ route('admin.notifications') }}" class="relative rounded-full p-2 text-slate-600 hover:bg-slate-100" aria-label="Notifications">
            <x-ui.icon name="bell" class="h-5 w-5" />
            @if (auth()->user()->unreadNotifications()->count())
                <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
            @endif
        </a>
        <div x-data="{ open: false }" class="relative">
            <button type="button" @click="open = !open" class="rounded-full ring-2 ring-white shadow" aria-label="Admin profile menu"><x-ui.avatar :user="auth()->user()" size="h-10 w-10" text-size="text-sm" /></button>
            <div x-cloak x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                <div class="border-b border-slate-100 px-3 py-2">
                    <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"><x-ui.icon name="user" class="h-4 w-4" /> My profile</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                        <x-ui.icon name="arrow-left-on-rectangle" class="h-4 w-4" /> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} | ResumeHub AI Admin</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|geist:500,600,700,800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/admin.js'])
    </head>
    <body class="min-h-screen bg-[#f7f9fc] font-sans text-[#121a2f] antialiased">
        <div x-data="{ sidebarOpen: false, collapsed: false }" class="min-h-screen">
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>
            @include('admin.partials.sidebar')

            <div class="min-h-screen transition-[padding] duration-200 lg:pl-[254px]" :style="collapsed ? 'padding-left:82px' : ''">
                @include('admin.partials.topbar', ['pageTitle' => $title])
                <main class="mx-auto w-full max-w-[1600px] px-4 py-5 sm:px-6 lg:px-7 lg:py-6">
                    @if (session('status'))
                        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ session('status') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>

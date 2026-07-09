<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body>
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-background">
            <div class="lg:flex">
                @include('partials.dashboard-sidebar', ['mode' => $mode])
                <div class="min-h-screen min-w-0 flex-1 lg:pl-72">
                    @include('partials.dashboard-topbar', ['mode' => $mode])
                    <main class="rh-container pb-24 pt-24 lg:pb-12">
                        {{ $slot }}
                    </main>
                    @include('partials.mobile-nav', ['mode' => $mode])
                </div>
            </div>
        </div>
    </body>
</html>

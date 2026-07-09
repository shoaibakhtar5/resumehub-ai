@props(['title' => 'ResumeHub AI'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="bg-background text-on-surface">
        <div class="min-h-screen">
            @include('partials.marketing-header')
            <main class="pt-16">
                {{ $slot }}
            </main>
            @include('partials.footer')
        </div>
    </body>
</html>

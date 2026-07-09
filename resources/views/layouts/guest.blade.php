<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body>
        <main class="min-h-screen bg-background">
            <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_520px]">
                <section class="hidden overflow-hidden bg-on-background text-white lg:block">
                    <div class="flex h-full flex-col justify-between p-10">
                        <x-brand href="{{ route('home') }}" class="text-white" />
                        <div class="max-w-xl">
                            <x-ui.badge variant="ai" icon="sparkles" class="bg-white/10 text-on-primary-container ring-white/20">{{ $eyebrow }}</x-ui.badge>
                            <h1 class="mt-8 font-display text-5xl font-bold leading-tight text-white">{{ $heading }}</h1>
                            <p class="mt-5 text-body-lg text-white/70">{{ $subheading }}</p>
                            <div class="mt-10 grid grid-cols-3 gap-4">
                                <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                    <p class="font-display text-2xl font-bold">98%</p>
                                    <p class="mt-1 text-body-sm text-white/65">ATS clarity</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                    <p class="font-display text-2xl font-bold">12k</p>
                                    <p class="mt-1 text-body-sm text-white/65">resumes built</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                    <p class="font-display text-2xl font-bold">4.9</p>
                                    <p class="mt-1 text-body-sm text-white/65">creator score</p>
                                </div>
                            </div>
                        </div>
                        <div class="rh-ai-border rounded-xl p-5 text-white">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white text-primary">
                                    <x-ui.icon name="sparkles" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="font-display text-label-md uppercase text-on-primary-container">AI resume co-pilot</p>
                                    <p class="text-body-sm text-white/70">Sign in to continue editing, scoring, and optimizing your career profile.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
                    <div class="w-full max-w-md">
                        <div class="mb-10 flex justify-center lg:hidden">
                            <x-brand href="{{ route('home') }}" />
                        </div>
                        <div class="rh-panel p-6 sm:p-8">
                            {{ $slot }}
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>

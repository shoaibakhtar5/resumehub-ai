@php
    $templates = $templates ?? collect();
    $categoryNames = collect(['All Categories'])->merge($categories ?? $templates->pluck('category.name')->filter()->unique())->values();
@endphp

<x-app-layout title="Resume Templates" mode="user">
    <div x-data="{ tab: 'popular', category: 'All Categories', search: '' }" class="space-y-8">
        <x-ui.page-header eyebrow="Templates" title="Premium Templates" description="Engineered by AI, designed for humans. Choose a layout to start your journey.">
            <div class="flex rounded-xl bg-surface-container p-1">
                <button type="button" class="rounded-lg px-4 py-2 font-display text-label-md transition" x-bind:class="tab === 'popular' ? 'bg-white text-primary shadow-soft' : 'text-on-surface-variant'" x-on:click="tab = 'popular'">Popular</button>
                <button type="button" class="rounded-lg px-4 py-2 font-display text-label-md transition" x-bind:class="tab === 'new' ? 'bg-white text-primary shadow-soft' : 'text-on-surface-variant'" x-on:click="tab = 'new'">New Arrivals</button>
            </div>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.card class="border-success/30 bg-success/10 text-on-surface">{{ session('status') }}</x-ui.card>
        @endif

        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
            <div class="rh-scrollbar-hide flex gap-3 overflow-x-auto pb-1 max-w-full">
                @foreach ($categoryNames as $label)
                    <button type="button" class="inline-flex shrink-0 items-center gap-2 rounded-full border px-5 py-2.5 font-display text-label-md transition" x-bind:class="category === '{{ $label }}' ? 'border-primary bg-primary text-white shadow-ai' : 'border-border-light bg-white text-on-surface-variant hover:border-primary/40 hover:text-primary'" x-on:click="category = '{{ $label }}'">
                        <x-ui.icon name="squares-2x2" class="h-5 w-5" />
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            
            <div class="flex w-full md:w-80 items-center gap-2.5 rounded-xl border border-outline-variant/40 bg-white px-3.5 py-2 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition">
                <x-ui.icon name="magnifying-glass" class="h-5 w-5 text-on-surface-variant" />
                <input type="text" x-model="search" placeholder="Search templates..." class="w-full border-0 bg-transparent p-0 text-xs focus:ring-0">
            </div>
        </div>

        @if ($templates->isEmpty())
            <x-ui.empty-state icon="squares-2x2" title="No templates yet" description="Seed or upload templates from the admin workspace to populate the gallery.">
                <x-ui.button href="{{ route('admin.templates') }}" icon="cloud-arrow-up">Manage Templates</x-ui.button>
            </x-ui.empty-state>
        @else
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($templates as $template)
                    @php($category = $template->category?->name ?? 'General')
                    <article x-show="(category === 'All Categories' || category === '{{ $category }}') && ('{{ strtolower($template->name) }}'.includes(search.toLowerCase()) || '{{ strtolower($template->description ?: '') }}'.includes(search.toLowerCase()))" class="group overflow-hidden rounded-xl border border-outline-variant/20 bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift">
                        <div class="relative aspect-[3/4] overflow-hidden bg-surface-container-low">
                            @if ($template->thumbnail_url)
                                <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }} Thumbnail" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="absolute inset-5 rounded-lg bg-white p-5 shadow-soft transition duration-500 group-hover:scale-105">
                                    <div class="h-4 w-24 rounded bg-on-surface"></div>
                                    <div class="mt-2 h-2 w-16 rounded bg-primary/40"></div>
                                    <div class="mt-8 grid grid-cols-[0.35fr_1fr] gap-4">
                                        <div class="space-y-3">
                                            <div class="h-16 w-16 rounded-full bg-surface-container-high"></div>
                                            <div class="h-2 rounded bg-surface-container-high"></div>
                                            <div class="h-2 rounded bg-surface-container-high"></div>
                                            <div class="h-2 rounded bg-surface-container-high"></div>
                                        </div>
                                        <div class="space-y-4">
                                            <div>
                                                <div class="h-2 w-20 rounded bg-primary/50"></div>
                                                <div class="mt-2 space-y-1.5">
                                                    <div class="h-2 rounded bg-surface-container-high"></div>
                                                    <div class="h-2 w-5/6 rounded bg-surface-container-high"></div>
                                                    <div class="h-2 w-2/3 rounded bg-surface-container-high"></div>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <div class="h-2 w-full rounded bg-primary/30"></div>
                                                <div class="h-2 w-4/5 rounded bg-primary/20"></div>
                                                <div class="h-2 w-3/5 rounded bg-primary/20"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($template->is_premium)
                                <x-ui.badge variant="ai" icon="sparkles" class="absolute left-4 top-4 bg-ai-accent text-white ring-0">Premium</x-ui.badge>
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center bg-on-background/40 opacity-0 backdrop-blur-sm transition group-hover:opacity-100">
                                <form method="POST" action="{{ route('resume.templates.apply', $template) }}">
                                    @csrf
                                    <x-ui.button type="submit" icon="sparkles">Use this Template</x-ui.button>
                                </form>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="font-display text-label-md text-on-surface">{{ $template->name }}</h2>
                                <span class="rounded bg-surface-container px-2 py-1 text-label-sm text-on-surface-variant">{{ $category }}</span>
                            </div>
                            <p class="mt-2 text-body-sm text-on-surface-variant">{{ $template->description ?: 'Clean, parser-friendly layout for polished resume exports.' }}</p>
                        </div>
                    </article>
                @endforeach

                <article class="rh-ai-border flex min-h-96 flex-col items-center justify-center rounded-xl p-8 text-center sm:col-span-2">
                    <span class="ai-glow inline-flex h-20 w-20 items-center justify-center rounded-full bg-white text-ai-accent">
                        <x-ui.icon name="sparkles" class="h-10 w-10" />
                    </span>
                    <h2 class="mt-6 font-display text-headline-lg text-on-surface">AI Custom Template</h2>
                    <p class="mt-3 max-w-sm text-body-md text-on-surface-variant">Describe your dream layout and let ResumeHub AI generate a tailored template direction.</p>
                    <x-ui.button href="{{ route('ai.studio') }}" class="mt-7" variant="dark" icon="sparkles">Generate with AI</x-ui.button>
                </article>
            </section>
        @endif
    </div>
</x-app-layout>

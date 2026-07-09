<x-marketing-layout :title="$post['title']">
    <article class="rh-container py-16 sm:py-20">
        <div class="max-w-3xl">
            <x-ui.badge>{{ $post['category'] }}</x-ui.badge>
            <h1 class="mt-6 font-display text-5xl font-bold leading-tight text-on-surface">{{ $post['title'] }}</h1>
            <p class="mt-5 text-body-lg text-on-surface-variant">{{ $post['excerpt'] }}</p>
            <p class="mt-5 text-label-md text-on-surface-variant">{{ $post['date'] }} - {{ $post['read'] }}</p>
        </div>
        <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="rh-card space-y-6 p-6 text-body-lg leading-8 text-on-surface-variant sm:p-8">
                @if (! empty($post['content']))
                    @foreach (preg_split('/\R{2,}/', trim(strip_tags($post['content']))) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @else
                    <p>Modern resume work is less about starting from a blank page and more about making sharp decisions quickly. A strong platform should help candidates understand what to emphasize, what to trim, and how to package evidence for a specific role.</p>
                    <p>ResumeHub AI combines structured editing, ATS checks, keyword coverage, and premium templates so every draft can move from rough content to polished export without leaving the workspace.</p>
                    <p>The best AI assistance is reviewable. Suggestions should be specific, measurable, and easy to accept or reject. That is why the interface keeps scoring, version history, previews, and exports close to the writing workflow.</p>
                @endif
            </div>
            <aside class="space-y-4">
                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Related workflow</h2>
                    <p class="mt-3 text-body-md text-on-surface-variant">Try the AI Studio, then run an ATS check before exporting a tracked resume link.</p>
                    <x-ui.button href="{{ route('register') }}" class="mt-5 w-full" iconAfter="arrow-right">Start building</x-ui.button>
                </x-ui.card>
            </aside>
        </div>
    </article>
</x-marketing-layout>

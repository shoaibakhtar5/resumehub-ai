<x-marketing-layout title="Blog">
    <section class="rh-container py-16 sm:py-20">
        <x-ui.page-header eyebrow="Blog" title="Resume strategy, AI workflows, and product updates." description="Practical writing, template, ATS, and interview guidance from the ResumeHub AI team." />
        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ($posts as $post)
                <a href="{{ route('blog.show', $post['slug']) }}" class="rh-card block p-6 transition hover:-translate-y-1 hover:shadow-lift rh-focus">
                    <x-ui.badge>{{ $post['category'] }}</x-ui.badge>
                    <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $post['title'] }}</h2>
                    <p class="mt-3 text-body-md text-on-surface-variant">{{ $post['excerpt'] }}</p>
                    <div class="mt-6 flex items-center justify-between text-label-sm text-on-surface-variant">
                        <span>{{ $post['date'] }}</span>
                        <span>{{ $post['read'] }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</x-marketing-layout>

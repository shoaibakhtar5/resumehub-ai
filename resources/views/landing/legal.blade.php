<x-marketing-layout :title="$page['title']">
    <section class="rh-container py-16 sm:py-20">
        <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :description="$page['description']" />
        <div class="mt-10 rh-card p-6 sm:p-8">
            <p class="font-display text-label-md uppercase text-primary">{{ $page['updated'] }}</p>
            <div class="mt-8 grid gap-8 text-body-md text-on-surface-variant md:grid-cols-2">
                @foreach (['Account and workspace access', 'Resume content and exports', 'AI-assisted generation', 'Privacy and shared links', 'Billing and subscriptions', 'Acceptable use and support'] as $section)
                    <section>
                        <h2 class="font-display text-headline-md text-on-surface">{{ $section }}</h2>
                        <p class="mt-3">ResumeHub AI provides tools for creating career documents, optimizing applications, and managing related workspace activity. Users remain responsible for the accuracy of submitted content and for reviewing AI-assisted suggestions before use.</p>
                    </section>
                @endforeach
            </div>
        </div>
    </section>
</x-marketing-layout>

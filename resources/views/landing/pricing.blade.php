<x-marketing-layout title="Pricing">
    <section class="rh-container py-16 sm:py-20">
        <x-ui.page-header eyebrow="Pricing" title="Plans for every stage of the job search." description="Start free, upgrade when you need unlimited tailoring, or equip a full career team with shared workflows." />
        <div class="mt-12 grid gap-5 lg:grid-cols-3">
            @foreach (config('resumehub.pricing_plans') as $plan)
                <x-ui.card class="{{ ! empty($plan['featured']) ? 'ring-2 ring-primary' : '' }}">
                    @if (! empty($plan['featured']))
                        <x-ui.badge variant="ai" icon="sparkles">Most popular</x-ui.badge>
                    @endif
                    <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $plan['name'] }}</h2>
                    <p class="mt-2 text-body-md text-on-surface-variant">{{ $plan['body'] }}</p>
                    <div class="mt-6 flex items-end gap-2">
                        <span class="font-display text-5xl font-bold text-on-surface">{{ $plan['price'] }}</span>
                        <span class="pb-2 text-body-sm text-on-surface-variant">/ month</span>
                    </div>
                    <div class="mt-7 grid gap-3">
                        @foreach ($plan['features'] as $feature)
                            <div class="flex items-center gap-3 text-body-md text-on-surface-variant">
                                <x-ui.icon name="check" class="h-5 w-5 text-success" />
                                <span>{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                    <x-ui.button href="{{ route('register') }}" class="mt-8 w-full" variant="{{ ! empty($plan['featured']) ? 'primary' : 'secondary' }}">Choose {{ $plan['name'] }}</x-ui.button>
                </x-ui.card>
            @endforeach
        </div>
    </section>
</x-marketing-layout>

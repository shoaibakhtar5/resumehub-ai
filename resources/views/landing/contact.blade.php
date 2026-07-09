<x-marketing-layout title="Contact">
    <section class="rh-container grid gap-10 py-16 lg:grid-cols-[0.9fr_1.1fr] lg:py-20">
        <div>
            <x-ui.page-header eyebrow="Contact" title="Talk to ResumeHub AI." description="Send a product, support, partnership, or team plan request. The admin inbox is designed to route each message cleanly." />
            <div class="mt-10 grid gap-4">
                @foreach ([['envelope', 'support@resumehub.ai'], ['phone', '+1 (555) 010-2400'], ['map-pin', 'Remote-first, serving global job seekers']] as $item)
                    <div class="flex items-center gap-4 rounded-xl border border-border-light bg-white p-4 shadow-soft">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-ui.icon :name="$item[0]" class="h-5 w-5" />
                        </span>
                        <p class="font-display text-label-md text-on-surface">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <form method="POST" action="{{ route('contact.submit') }}" class="rh-panel grid gap-5 p-6 sm:p-8">
            @csrf
            @if (session('status'))
                <div class="rounded-lg border border-success/30 bg-success/10 p-4 text-body-sm text-on-surface">{{ session('status') }}</div>
            @endif
            <x-ui.input label="Full name" name="name" type="text" :value="old('name')" required autocomplete="name" :error="$errors->first('name')" />
            <x-ui.input label="Email address" name="email" type="email" :value="old('email')" required autocomplete="email" :error="$errors->first('email')" />
            <x-ui.input label="Phone" name="phone" type="tel" :value="old('phone')" autocomplete="tel" :error="$errors->first('phone')" />
            <x-ui.select label="Topic" name="topic" :options="['support' => 'Product support', 'sales' => 'Team pricing', 'partnership' => 'Partnership', 'feedback' => 'Product feedback']" :selected="old('topic', 'support')" />
            <x-ui.textarea label="Message" name="message" required :error="$errors->first('message')">{{ old('message') }}</x-ui.textarea>
            <x-ui.button type="submit" iconAfter="arrow-right">Send message</x-ui.button>
        </form>
    </section>
</x-marketing-layout>

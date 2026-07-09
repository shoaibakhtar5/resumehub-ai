<footer class="border-t border-border-light bg-surface">
    <div class="rh-container grid gap-10 py-12 md:grid-cols-[1.4fr_repeat(3,1fr)]">
        <div>
            <x-brand />
            <p class="mt-4 max-w-sm text-body-sm text-on-surface-variant">AI-powered resume building, ATS scoring, cover letters, and interview prep in one polished career workspace.</p>
        </div>
        <div>
            <p class="font-display text-label-md text-on-surface">Product</p>
            <div class="mt-4 grid gap-3 text-body-sm text-on-surface-variant">
                <a href="{{ route('features') }}" class="hover:text-primary">Features</a>
                <a href="{{ route('pricing') }}" class="hover:text-primary">Pricing</a>
                <a href="{{ route('faq') }}" class="hover:text-primary">FAQ</a>
            </div>
        </div>
        <div>
            <p class="font-display text-label-md text-on-surface">Company</p>
            <div class="mt-4 grid gap-3 text-body-sm text-on-surface-variant">
                <a href="{{ route('about') }}" class="hover:text-primary">About</a>
                <a href="{{ route('blog.index') }}" class="hover:text-primary">Blog</a>
                <a href="{{ route('contact') }}" class="hover:text-primary">Contact</a>
            </div>
        </div>
        <div>
            <p class="font-display text-label-md text-on-surface">Legal</p>
            <div class="mt-4 grid gap-3 text-body-sm text-on-surface-variant">
                <a href="{{ route('terms') }}" class="hover:text-primary">Terms</a>
                <a href="{{ route('privacy') }}" class="hover:text-primary">Privacy</a>
            </div>
        </div>
    </div>
    <div class="border-t border-border-light">
        <div class="rh-container flex flex-col gap-3 py-5 text-body-sm text-on-surface-variant sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} ResumeHub AI. Crafted for career momentum.</p>
            <p>Premium templates, intelligent editing, measurable outcomes.</p>
        </div>
    </div>
</footer>

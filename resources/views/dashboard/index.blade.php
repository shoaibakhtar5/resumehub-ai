<x-app-layout title="Dashboard" mode="user">
    <div class="space-y-8">
        <section class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
            <div>
                <p class="font-display text-label-md uppercase text-primary">AI studio active</p>
                <h1 class="mt-3 font-display text-4xl font-bold text-on-surface sm:text-5xl">Welcome back, {{ auth()->user()->name ?? 'Alex' }}</h1>
                <p class="mt-3 text-body-lg text-on-surface-variant">Ready to land your dream role today?</p>
                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <x-ui.button href="{{ route('resume.builder') }}" size="lg" icon="plus-circle">Create New</x-ui.button>
                    <x-ui.button href="{{ route('ats.checker') }}" size="lg" variant="secondary" icon="clipboard-document-check">ATS Check</x-ui.button>
                </div>
            </div>
            <div class="rounded-xl bg-on-background p-7 text-white shadow-panel sm:p-8">
                <x-ui.badge variant="ai" icon="sparkles" class="bg-white/10 text-on-primary-container ring-white/20">AI Studio Active</x-ui.badge>
                <h2 class="mt-7 font-display text-4xl font-bold leading-tight sm:text-5xl">Elevate your profile with AI-driven precision.</h2>
                <p class="mt-5 text-body-lg text-white/70">Use your saved resumes, ATS scans, and AI history to tailor the next draft with sharper evidence.</p>
                <x-ui.button href="{{ route('ai.studio') }}" variant="white" class="mt-8 rounded-full" iconAfter="arrow-right">Launch AI Studio</x-ui.button>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-4">
            <x-ui.stat-card label="Resume score" value="{{ $stats['resume_score'] ?? 0 }}%" icon="chart-bar" tone="success" />
            <x-ui.stat-card label="Latest ATS score" value="{{ $stats['ats_score'] ?? 0 }}%" icon="shield-check" />
            <x-ui.stat-card label="AI rewrites" value="{{ $stats['ai_rewrites'] ?? 0 }}" icon="sparkles" tone="ai" />
            <x-ui.stat-card label="Share links" value="{{ $stats['recruiter_opens'] ?? 0 }}" icon="eye" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <x-ui.card>
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="font-display text-headline-md text-on-surface">Recent resumes</h2>
                        <p class="mt-1 text-body-sm text-on-surface-variant">Active drafts, exports, and score movement.</p>
                    </div>
                    <x-ui.button href="{{ route('resumes.index') }}" variant="ghost" size="sm">View all</x-ui.button>
                </div>
                <div class="mt-6 space-y-4">
                    @forelse (($resumes ?? collect()) as $resume)
                        <a href="{{ route('resumes.edit', $resume) }}" class="flex flex-col gap-3 rounded-lg border border-border-light p-4 transition hover:bg-surface-subtle sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-display text-label-md text-on-surface">{{ $resume->title }}</h3>
                                <p class="mt-1 text-body-sm text-on-surface-variant">{{ $resume->target_role ?: 'General resume' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-ui.badge variant="success">{{ $resume->completion_score }}%</x-ui.badge>
                                <span class="text-body-sm text-on-surface-variant">{{ $resume->updated_at?->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <x-ui.empty-state icon="document-text" title="No resumes yet" description="Create your first resume to start tracking scores, exports, and AI suggestions.">
                            <x-ui.button href="{{ route('resume.builder') }}" icon="plus-circle">Create Resume</x-ui.button>
                        </x-ui.empty-state>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-display text-headline-md text-on-surface">Optimization pulse</h2>
                <div class="mt-6 space-y-5">
                    <x-ui.progress label="ATS readiness" value="94" />
                    <x-ui.progress label="Keyword coverage" value="88" />
                    <x-ui.progress label="Impact density" value="82" />
                </div>
                <div class="mt-7 rounded-xl border border-ai-accent/20 bg-ai-accent/10 p-5">
                    <div class="flex gap-3">
                        <x-ui.icon name="sparkles" class="h-6 w-6 text-ai-accent" />
                        <div>
                            <p class="font-display text-label-md text-on-surface">AI suggestion</p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">Run an ATS scan after your next save to refresh keyword and formatting guidance.</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </section>
    </div>
</x-app-layout>

@php
    $resume?->loadMissing([
        'profile',
        'summary',
        'socialLinks',
        'experiences',
        'educations',
        'projects',
        'skills',
        'languages',
        'certifications',
        'awards',
        'references',
        'customSections.items',
        'sections',
        'shares',
        'template',
    ]);

    $settings = $resume?->settings ?? [];
    $profile = $resume?->profile;
    $sharedView = $sharedView ?? false;
    $theme = $settings['theme'] ?? [];
    $accent = $theme['accent_color'] ?? ($resume?->template?->config['accent_color'] ?? '#3525cd');
    $visible = fn (string $key) => ! $resume || ! $resume->sections->firstWhere('section_key', $key) || $resume->sections->firstWhere('section_key', $key)?->is_visible;
    $order = fn (string $key) => 10 + (int) ($resume?->sections?->firstWhere('section_key', $key)?->sort_order ?? 0);
    $skills = $settings['skills'] ?? $resume?->skills?->pluck('name')->all() ?? [];
    $languages = $settings['languages'] ?? $resume?->languages?->pluck('name')->all() ?? [];
@endphp

<x-app-layout title="Resume Preview" mode="user">
    @if (! $resume)
        <x-ui.empty-state icon="document-text" title="No resume to preview" description="Create your first resume, then return here to export, share, and review it.">
            <x-ui.button href="{{ route('resume.builder') }}" icon="plus-circle">Create Resume</x-ui.button>
        </x-ui.empty-state>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <section class="rh-panel p-4">
                <div class="mx-auto flex max-w-3xl flex-col rounded-lg bg-white p-8 shadow-lift sm:p-12" style="--resume-accent: {{ $accent }}">
                    <div class="flex flex-col gap-5 border-b border-border-light pb-6 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h1 class="font-display text-5xl font-bold text-on-surface">{{ $profile?->full_name ?: $resume->title }}</h1>
                            <p class="mt-2" style="color: var(--resume-accent)">{{ $profile?->headline ?: $resume->target_role ?: 'Resume' }}</p>
                            @if ($profile?->website)
                                <a href="{{ $profile->website }}" class="mt-2 inline-block text-body-sm text-on-surface-variant">{{ $profile->website }}</a>
                            @endif
                        </div>
                        <div class="text-body-sm text-on-surface-variant sm:text-right">
                            @if ($profile?->email)<p>{{ $profile->email }}</p>@endif
                            @if ($profile?->phone)<p>{{ $profile->phone }}</p>@endif
                            @if ($profile?->location)<p>{{ $profile->location }}</p>@endif
                            @foreach ($resume->socialLinks->where('is_visible', true) as $link)
                                <p><a href="{{ $link->url }}">{{ $link->label ?: ucfirst($link->platform) }}</a></p>
                            @endforeach
                        </div>
                    </div>

                    @if ($visible('summary'))
                        <section class="mt-7" style="order: {{ $order('summary') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Profile</h2>
                            <p class="mt-3 text-body-md leading-7 text-on-surface-variant">{{ $resume->summary?->content ?? $settings['summary'] ?? 'Add a targeted summary in the builder to complete this section.' }}</p>
                        </section>
                    @endif

                    @if ($visible('experience'))
                        <section class="mt-7" style="order: {{ $order('experience') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Experience</h2>
                            <div class="mt-3 space-y-5">
                                @forelse ($resume->experiences->where('is_visible', true) as $experience)
                                    <article>
                                        <div class="flex flex-col justify-between gap-1 sm:flex-row">
                                            <p class="font-display text-label-md text-on-surface">{{ $experience->position }} - {{ $experience->company }}</p>
                                            <p class="text-body-sm text-on-surface-variant">{{ trim(optional($experience->start_date)->format('M Y').' - '.($experience->is_current ? 'Present' : optional($experience->end_date)->format('M Y'))) }}</p>
                                        </div>
                                        @if ($experience->location)<p class="mt-1 text-body-sm text-on-surface-variant">{{ $experience->location }}</p>@endif
                                        <p class="mt-2 whitespace-pre-line text-body-sm leading-6 text-on-surface-variant">{{ $experience->description }}</p>
                                    </article>
                                @empty
                                    <p class="text-body-md text-on-surface-variant">No experience added yet.</p>
                                @endforelse
                            </div>
                        </section>
                    @endif

                    @if ($visible('education'))
                        <section class="mt-7" style="order: {{ $order('education') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Education</h2>
                            <div class="mt-3 space-y-3">
                                @forelse ($resume->educations->where('is_visible', true) as $education)
                                    <article>
                                        <p class="font-display text-label-md text-on-surface">{{ trim($education->degree.' '.$education->field_of_study) ?: $education->institution }}</p>
                                        <p class="text-body-sm text-on-surface-variant">{{ $education->institution }}{{ $education->grade ? ' - '.$education->grade : '' }}</p>
                                        @if ($education->description)<p class="mt-1 text-body-sm text-on-surface-variant">{{ $education->description }}</p>@endif
                                    </article>
                                @empty
                                    <p class="text-body-md text-on-surface-variant">No education added yet.</p>
                                @endforelse
                            </div>
                        </section>
                    @endif

                    @if ($visible('skills'))
                        <section class="mt-7" style="order: {{ $order('skills') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Skills</h2>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse ($skills as $skill)
                                    <span class="rounded-md bg-surface-container px-2.5 py-1 text-label-sm text-on-surface">{{ $skill }}</span>
                                @empty
                                    <p class="text-body-md text-on-surface-variant">No skills added yet.</p>
                                @endforelse
                            </div>
                        </section>
                    @endif

                    @if ($visible('projects'))
                        <section class="mt-7" style="order: {{ $order('projects') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Projects</h2>
                            <div class="mt-3 space-y-5">
                                @foreach ($resume->projects->where('is_visible', true) as $project)
                                    <article>
                                        <p class="font-display text-label-md text-on-surface">{{ $project->name }}{{ $project->role ? ' - '.$project->role : '' }}</p>
                                        @if ($project->url)<a href="{{ $project->url }}" class="text-body-sm" style="color: var(--resume-accent)">{{ $project->url }}</a>@endif
                                        <p class="mt-2 whitespace-pre-line text-body-sm leading-6 text-on-surface-variant">{{ $project->description }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($visible('languages') && $languages !== [])
                        <section class="mt-7" style="order: {{ $order('languages') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Languages</h2>
                            <p class="mt-3 text-body-md leading-7 text-on-surface-variant">{{ implode(', ', $languages) }}</p>
                        </section>
                    @endif

                    @if ($visible('certifications'))
                        <section class="mt-7" style="order: {{ $order('certifications') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Certifications</h2>
                            <div class="mt-3 space-y-3">
                                @foreach ($resume->certifications->where('is_visible', true) as $certification)
                                    <article>
                                        <p class="font-display text-label-md text-on-surface">{{ $certification->name }}</p>
                                        <p class="text-body-sm text-on-surface-variant">{{ $certification->issuer }}{{ $certification->issued_at ? ' - '.$certification->issued_at->format('Y') : '' }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($visible('awards'))
                        <section class="mt-7" style="order: {{ $order('awards') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">Awards</h2>
                            <div class="mt-3 space-y-3">
                                @foreach ($resume->awards->where('is_visible', true) as $award)
                                    <article>
                                        <p class="font-display text-label-md text-on-surface">{{ $award->title }}</p>
                                        <p class="text-body-sm text-on-surface-variant">{{ $award->issuer }}{{ $award->awarded_at ? ' - '.$award->awarded_at->format('Y') : '' }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($visible('references'))
                        <section class="mt-7" style="order: {{ $order('references') }}">
                            <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">References</h2>
                            <div class="mt-3 space-y-3">
                                @foreach ($resume->references->where('is_visible', true) as $reference)
                                    <article>
                                        <p class="font-display text-label-md text-on-surface">{{ $reference->name }}</p>
                                        <p class="text-body-sm text-on-surface-variant">{{ trim($reference->title.' '.$reference->company) ?: ($reference->available_on_request ? 'Available on request' : '') }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($visible('custom_sections'))
                        @foreach ($resume->customSections->where('is_visible', true) as $section)
                            <section class="mt-7" style="order: {{ $order('custom_sections') + (int) $section->sort_order }}">
                                <h2 class="font-display text-label-md uppercase" style="color: var(--resume-accent)">{{ $section->title }}</h2>
                                @if ($section->description)<p class="mt-3 text-body-sm text-on-surface-variant">{{ $section->description }}</p>@endif
                                <div class="mt-3 space-y-3">
                                    @foreach ($section->items->where('is_visible', true) as $item)
                                        <article>
                                            <p class="font-display text-label-md text-on-surface">{{ $item->title }}</p>
                                            @if ($item->subtitle)<p class="text-body-sm text-on-surface-variant">{{ $item->subtitle }}</p>@endif
                                            @if ($item->description)<p class="mt-1 text-body-sm text-on-surface-variant">{{ $item->description }}</p>@endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    @endif
                </div>
            </section>

            <aside class="space-y-5">
                @if (session('status'))
                    <x-ui.card class="border-success/30 bg-success/10 text-on-surface">{{ session('status') }}</x-ui.card>
                @endif

                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Export Readiness</h2>
                    <div class="mt-5 space-y-4">
                        <x-ui.progress label="Resume completion" :value="$resume->completion_score" />
                        <x-ui.progress label="ATS fit" :value="$latestReport ? (int) $latestReport->ats_score : 0" />
                        <x-ui.progress label="Keyword coverage" :value="$latestReport ? (int) $latestReport->keyword_score : 0" />
                    </div>
                    @unless ($sharedView)
                        <div class="mt-6 grid gap-3">
                            <form method="POST" action="{{ route('resumes.download', $resume) }}">
                                @csrf
                                <input type="hidden" name="format" value="txt">
                                <x-ui.button type="submit" icon="arrow-down-tray" class="w-full">Download Text</x-ui.button>
                            </form>
                            <form method="POST" action="{{ route('resumes.share', $resume) }}" class="grid gap-3">
                                @csrf
                                <input type="hidden" name="visibility" value="unlisted">
                                <label class="inline-flex items-center gap-2 text-body-sm text-on-surface-variant">
                                    <input type="checkbox" name="allow_download" value="1" class="rounded border-border-light text-primary focus:ring-primary">
                                    Allow downloads
                                </label>
                                <x-ui.button type="submit" variant="secondary" icon="share" class="w-full">Create Share Link</x-ui.button>
                            </form>
                        </div>
                    @endunless
                </x-ui.card>

                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">ATS Keywords</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($latestReport?->keywords ?? [] as $keyword)
                            <x-ui.badge :variant="$keyword->status === 'matched' ? 'success' : 'warning'">{{ $keyword->keyword }}</x-ui.badge>
                        @empty
                            <p class="text-body-md text-on-surface-variant">Run an ATS scan to get keyword guidance.</p>
                        @endforelse
                    </div>
                    @unless ($sharedView)
                        <x-ui.button href="{{ route('ats.checker') }}" class="mt-5 w-full" variant="secondary" icon="shield-check">Run ATS Check</x-ui.button>
                    @endunless
                </x-ui.card>
            </aside>
        </div>
    @endif
</x-app-layout>

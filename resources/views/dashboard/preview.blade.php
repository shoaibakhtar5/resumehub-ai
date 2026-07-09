@php
    $settings = $resume?->settings ?? [];
    $profile = $resume?->profile;
    $sharedView = $sharedView ?? false;
@endphp

<x-app-layout title="Resume Preview" mode="user">
    @if (! $resume)
        <x-ui.empty-state icon="document-text" title="No resume to preview" description="Create your first resume, then return here to export, share, and review it.">
            <x-ui.button href="{{ route('resume.builder') }}" icon="plus-circle">Create Resume</x-ui.button>
        </x-ui.empty-state>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <section class="rh-panel p-4">
                <div class="mx-auto max-w-3xl rounded-lg bg-white p-8 shadow-lift sm:p-12">
                    <div class="flex flex-col gap-5 border-b border-border-light pb-6 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h1 class="font-display text-5xl font-bold text-on-surface">{{ $profile?->full_name ?: $resume->title }}</h1>
                            <p class="mt-2 text-primary">{{ $profile?->headline ?: $resume->target_role ?: 'Resume' }}</p>
                        </div>
                        <div class="text-body-sm text-on-surface-variant sm:text-right">
                            @if ($profile?->email)<p>{{ $profile->email }}</p>@endif
                            @if ($profile?->phone)<p>{{ $profile->phone }}</p>@endif
                            @if ($profile?->location)<p>{{ $profile->location }}</p>@endif
                        </div>
                    </div>

                    <section class="mt-7">
                        <h2 class="font-display text-label-md uppercase text-primary">Profile</h2>
                        <p class="mt-3 text-body-md leading-7 text-on-surface-variant">{{ $settings['summary'] ?? 'Add a targeted summary in the builder to complete this section.' }}</p>
                    </section>

                    <section class="mt-7">
                        <h2 class="font-display text-label-md uppercase text-primary">Experience</h2>
                        <div class="mt-3 space-y-5">
                            @forelse ($resume->experiences as $experience)
                                <article>
                                    <p class="font-display text-label-md text-on-surface">{{ $experience->position }} - {{ $experience->company }}</p>
                                    <p class="mt-1 text-body-sm text-on-surface-variant">{{ $experience->description }}</p>
                                </article>
                            @empty
                                <p class="text-body-md text-on-surface-variant">No experience added yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="mt-7">
                        <h2 class="font-display text-label-md uppercase text-primary">Education</h2>
                        <div class="mt-3 space-y-3">
                            @forelse ($resume->educations as $education)
                                <p class="text-body-md text-on-surface-variant">{{ trim($education->degree.' '.$education->field_of_study) }} - {{ $education->institution }}</p>
                            @empty
                                <p class="text-body-md text-on-surface-variant">No education added yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="mt-7">
                        <h2 class="font-display text-label-md uppercase text-primary">Skills</h2>
                        <p class="mt-3 text-body-md leading-7 text-on-surface-variant">{{ implode(', ', $settings['skills'] ?? []) ?: 'No skills added yet.' }}</p>
                    </section>
                </div>
            </section>

            <aside class="space-y-5">
                @if (session('status'))
                    <x-ui.card class="border-success/30 bg-success/10 text-on-surface">{{ session('status') }}</x-ui.card>
                @endif

                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Export readiness</h2>
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
                    <h2 class="font-display text-headline-md text-on-surface">AI notes</h2>
                    <p class="mt-3 text-body-md text-on-surface-variant">
                        @if ($latestReport)
                            Latest ATS scan is {{ round($latestReport->ats_score) }}%. Review missing keywords before exporting.
                        @else
                            Run an ATS scan to get keyword, formatting, content, and readability guidance.
                        @endif
                    </p>
                    @unless ($sharedView)
                        <x-ui.button href="{{ route('ats.checker') }}" class="mt-5 w-full" variant="secondary" icon="shield-check">Run ATS Check</x-ui.button>
                    @endunless
                </x-ui.card>
            </aside>
        </div>
    @endif
</x-app-layout>

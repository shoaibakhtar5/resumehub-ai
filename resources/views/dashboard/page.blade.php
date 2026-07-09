<x-app-layout :title="$page['title']" mode="user">
    <div class="space-y-8">
        <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :description="$page['description']">
            <x-ui.button href="{{ route('resume.builder') }}" icon="plus-circle">Create New</x-ui.button>
            <x-ui.button href="{{ route('ats.checker') }}" variant="secondary" icon="shield-check">Run ATS Check</x-ui.button>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.card class="border-success/30 bg-success/10 text-on-surface">{{ session('status') }}</x-ui.card>
        @endif

        @if (session('ai_output'))
            <x-ui.card class="border-ai-accent/30 bg-ai-accent/10">
                <h2 class="font-display text-headline-md text-on-surface">Latest AI output</h2>
                <p class="mt-3 whitespace-pre-line text-body-md leading-7 text-on-surface-variant">{{ session('ai_output') }}</p>
            </x-ui.card>
        @endif

        <section class="grid gap-5 md:grid-cols-3 xl:grid-cols-4">
            @foreach ($page['stats'] as $stat)
                <x-ui.stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :icon="$stat['icon']"
                    :trend="$stat['trend'] ?? null"
                    :tone="$stat['tone'] ?? 'primary'"
                />
            @endforeach
        </section>

        @isset($resumes)
            @if (request()->routeIs('ats.checker') || request()->routeIs('ats.reports.show'))
                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Run ATS scan</h2>
                    <form method="POST" action="{{ route('ats.reports.store') }}" class="mt-6 grid gap-5 lg:grid-cols-2">
                        @csrf
                        <x-ui.select label="Resume" name="resume_id" :options="['' => 'Paste resume text instead'] + $resumes->pluck('title', 'id')->all()" :selected="old('resume_id')" />
                        <x-ui.input label="Target Job Title" name="target_job_title" type="text" :value="old('target_job_title')" />
                        <x-ui.textarea label="Job Description" name="job_description" class="lg:col-span-2">{{ old('job_description') }}</x-ui.textarea>
                        <x-ui.textarea label="Resume Text" name="resume_text" class="lg:col-span-2">{{ old('resume_text') }}</x-ui.textarea>
                        <x-ui.button type="submit" icon="shield-check">Scan Resume</x-ui.button>
                    </form>
                </x-ui.card>

                @isset($reports)
                    <section class="grid gap-5 lg:grid-cols-2">
                        @foreach ($reports as $report)
                            <x-ui.card>
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="font-display text-headline-md text-on-surface">{{ $report->resume?->title ?? 'ATS Report' }}</h2>
                                        <p class="mt-1 text-body-sm text-on-surface-variant">{{ $report->scanned_at?->diffForHumans() }}</p>
                                    </div>
                                    <x-ui.badge variant="success">{{ round($report->ats_score) }}%</x-ui.badge>
                                </div>
                                <div class="mt-5 space-y-4">
                                    <x-ui.progress label="Keyword score" :value="(int) $report->keyword_score" />
                                    <x-ui.progress label="Formatting score" :value="(int) $report->formatting_score" />
                                    <x-ui.progress label="Content score" :value="(int) $report->content_score" />
                                </div>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($report->keywords->take(8) as $keyword)
                                        <x-ui.badge :variant="$keyword->status === 'matched' ? 'success' : 'warning'">{{ $keyword->keyword }}</x-ui.badge>
                                    @endforeach
                                </div>
                            </x-ui.card>
                        @endforeach
                    </section>
                @endisset
            @else
                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Generate with AI</h2>
                    <form method="POST" action="{{ route('ai.generate') }}" class="mt-6 grid gap-5 lg:grid-cols-2">
                        @csrf
                        <input type="hidden" name="feature" value="{{ request()->segment(1) ?: 'ai-resume-studio' }}">
                        <x-ui.select label="Resume" name="resume_id" :options="['' => 'No resume context'] + $resumes->pluck('title', 'id')->all()" :selected="old('resume_id')" />
                        <x-ui.select label="Action" name="action" :options="[
                            'summary' => 'Summary',
                            'experience' => 'Experience bullets',
                            'skills' => 'Skills',
                            'cover_letter' => 'Cover letter',
                            'interview_questions' => 'Interview questions',
                            'review' => 'Resume review',
                            'score' => 'Resume score',
                            'keywords' => 'Keywords',
                        ]" :selected="old('action', 'summary')" />
                        <x-ui.input label="Tone" name="tone" type="text" value="{{ old('tone', 'confident') }}" />
                        <x-ui.textarea label="Prompt or role notes" name="input" class="lg:col-span-2">{{ old('input') }}</x-ui.textarea>
                        <x-ui.textarea label="Job Description" name="job_description" class="lg:col-span-2">{{ old('job_description') }}</x-ui.textarea>
                        <x-ui.button type="submit" icon="sparkles">Generate</x-ui.button>
                    </form>
                </x-ui.card>

                @isset($aiHistories)
                    <section class="grid gap-5 lg:grid-cols-2">
                        @foreach ($aiHistories as $history)
                            <x-ui.card>
                                <x-ui.badge variant="ai">{{ $history->title }}</x-ui.badge>
                                <p class="mt-4 whitespace-pre-line text-body-md leading-7 text-on-surface-variant">{{ $history->output }}</p>
                            </x-ui.card>
                        @endforeach
                    </section>
                @endisset
            @endif
        @endisset

        @isset($records)
            <section class="grid gap-5 lg:grid-cols-2">
                @forelse ($records as $record)
                    <x-ui.card>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="font-display text-headline-md text-on-surface">{{ $record->title ?? $record->name ?? 'Record' }}</h2>
                                <p class="mt-1 text-body-sm text-on-surface-variant">{{ $record->target_role ?? $record->status ?? $record->updated_at?->diffForHumans() }}</p>
                            </div>
                            @if (isset($record->completion_score))
                                <x-ui.badge variant="success">{{ $record->completion_score }}%</x-ui.badge>
                            @endif
                        </div>
                        @if ($record instanceof \App\Models\Resume)
                            <div class="mt-5 flex flex-wrap gap-2">
                                <x-ui.button href="{{ route('resumes.edit', $record) }}" size="sm" icon="pencil-square">Edit</x-ui.button>
                                <x-ui.button href="{{ route('resume.preview', $record) }}" size="sm" variant="secondary" icon="eye">Preview</x-ui.button>
                                <form method="POST" action="{{ route('resumes.favorite', $record) }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="ghost" icon="heart">Favorite</x-ui.button>
                                </form>
                                <form method="POST" action="{{ $record->is_archived ? route('resumes.restore', $record) : route('resumes.archive', $record) }}">
                                    @csrf
                                    <x-ui.button type="submit" size="sm" variant="ghost" icon="{{ $record->is_archived ? 'arrow-path' : 'archive-box' }}">{{ $record->is_archived ? 'Restore' : 'Archive' }}</x-ui.button>
                                </form>
                            </div>
                        @endif
                    </x-ui.card>
                @empty
                    <x-ui.empty-state icon="document-text" title="Nothing here yet" description="Create or save a resume to populate this workspace." />
                @endforelse
            </section>

            @if (method_exists($records, 'links'))
                <div>{{ $records->links() }}</div>
            @endif
        @else
            <section class="grid gap-5 lg:grid-cols-3">
                @foreach ($page['cards'] as $card)
                    <x-ui.card interactive>
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-ui.icon :name="$card['icon']" class="h-5 w-5" />
                        </span>
                        <h2 class="mt-5 font-display text-headline-md text-on-surface">{{ $card['title'] }}</h2>
                        <p class="mt-3 text-body-md text-on-surface-variant">{{ $card['body'] }}</p>
                    </x-ui.card>
                @endforeach
            </section>

            <x-ui.table :headers="$page['table']['headers']" :rows="$page['table']['rows']" />
        @endisset
    </div>
</x-app-layout>

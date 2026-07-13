@php
    $profile = is_array($payload['profile'] ?? null) ? $payload['profile'] : [];
    $theme = is_array($payload['theme'] ?? null) ? $payload['theme'] : [];
    $summary = is_array($payload['summary'] ?? null)
        ? ($payload['summary']['content'] ?? '')
        : (string) ($payload['summary'] ?? '');

    $isVisible = static function (array $item): bool {
        if (! array_key_exists('is_visible', $item)) {
            return true;
        }

        return filter_var($item['is_visible'], FILTER_VALIDATE_BOOL);
    };

    $hasText = static fn (mixed $value): bool => is_scalar($value) && trim((string) $value) !== '';
    $rows = static fn (mixed $items) => collect(is_array($items) ? $items : [])
        ->filter(fn ($item) => is_array($item) && $isVisible($item))
        ->values();

    $sectionMap = $rows($payload['sections'] ?? [])->keyBy('section_key');
    $sectionVisible = static function (string $key) use ($sectionMap): bool {
        $section = $sectionMap->get($key);

        return ! is_array($section) || ! array_key_exists('is_visible', $section)
            || filter_var($section['is_visible'], FILTER_VALIDATE_BOOL);
    };
    $sectionOrder = static fn (string $key, int $fallback): int => (int) ($sectionMap->get($key)['sort_order'] ?? $fallback);
    $sectionTitle = static fn (string $key, string $fallback): string => trim((string) ($sectionMap->get($key)['title'] ?? '')) ?: $fallback;

    $skills = collect(is_array($payload['skills'] ?? null) ? $payload['skills'] : [])
        ->map(fn ($skill) => is_array($skill) ? $skill : ['name' => $skill])
        ->filter(fn ($skill) => $isVisible($skill) && $hasText($skill['name'] ?? null))
        ->values();
    $languages = $rows($payload['languages'] ?? [])->filter(fn ($item) => $hasText($item['name'] ?? null))->values();
    $experiences = $rows($payload['experiences'] ?? [])->filter(fn ($item) => $hasText($item['company'] ?? null) || $hasText($item['position'] ?? null))->values();
    $educations = $rows($payload['educations'] ?? [])->filter(fn ($item) => $hasText($item['institution'] ?? null) || $hasText($item['degree'] ?? null))->values();
    $projects = $rows($payload['projects'] ?? [])->filter(fn ($item) => $hasText($item['name'] ?? null))->values();
    $certifications = $rows($payload['certifications'] ?? [])->filter(fn ($item) => $hasText($item['name'] ?? null))->values();
    $awards = $rows($payload['awards'] ?? [])->filter(fn ($item) => $hasText($item['title'] ?? null))->values();
    $references = $rows($payload['references'] ?? [])->filter(fn ($item) => $hasText($item['name'] ?? null) || ! empty($item['available_on_request']))->values();
    $customSections = $rows($payload['custom_sections'] ?? [])->filter(fn ($item) => $hasText($item['title'] ?? null))->values();
    $socialLinks = $rows($payload['social_links'] ?? [])->filter(fn ($item) => $hasText($item['url'] ?? null))->values();

    $splitTokens = static function (mixed $value): \Illuminate\Support\Collection {
        if (is_array($value)) {
            $value = implode(',', array_filter($value, 'is_scalar'));
        }

        return collect(preg_split('/[,\n]+/', (string) $value) ?: [])
            ->map(fn ($token) => trim($token))
            ->filter()
            ->values();
    };

    $displayUrl = static function (mixed $url): string {
        $value = trim((string) $url);

        return preg_replace('#^https?://#i', '', rtrim($value, '/')) ?: $value;
    };

    $name = trim((string) ($profile['full_name'] ?? '')) ?: trim((string) ($payload['title'] ?? ''));
    $headline = trim((string) ($profile['headline'] ?? '')) ?: trim((string) ($payload['target_role'] ?? ''));
    $photo = $profile['photo_path'] ?? null;
    $photoUrl = $photo && ! str_starts_with($photo, 'http') && ! str_starts_with($photo, 'blob:') && ! str_starts_with($photo, 'data:')
        ? asset(ltrim($photo, '/'))
        : $photo;
    $initial = mb_strtoupper(mb_substr($name ?: 'R', 0, 1));
    $fontPairing = in_array($theme['font_pairing'] ?? null, ['modern', 'classic', 'executive', 'technical'], true)
        ? $theme['font_pairing']
        : 'modern';

    $contacts = collect([
        ['icon' => 'envelope', 'value' => $profile['email'] ?? null],
        ['icon' => 'phone', 'value' => $profile['phone'] ?? null],
        ['icon' => 'map-pin', 'value' => $profile['location'] ?? null],
        ['icon' => 'globe-alt', 'value' => $profile['website'] ?? null, 'display' => $displayUrl($profile['website'] ?? null)],
    ])->filter(fn ($contact) => $hasText($contact['value'] ?? null));
@endphp

<div
    wire:key="resume-preview-{{ $resumeId ?: 'draft' }}"
    class="resume-live-sheet resume-template-{{ $templateVariant }} resume-font-{{ $fontPairing }} resume-density-{{ $density }} overflow-hidden rounded-lg border border-slate-200 bg-white shadow-soft"
    style="--resume-accent: {{ $accent }}"
    data-template="{{ $templateSlug }}"
>
    <div class="resume-live-document">
        <aside class="resume-preview-sidebar">
            <header class="resume-preview-identity">
                <div
                    class="resume-preview-photo"
                    wire:ignore
                    x-data="{ livePhoto: @js($photoUrl) }"
                    x-on:resume-photo-selected.window="livePhoto = $event.detail"
                >
                    <template x-if="livePhoto">
                        <img x-bind:src="livePhoto" alt="{{ $name ?: 'Profile photo' }}">
                    </template>
                    <template x-if="!livePhoto">
                        <span x-text="typeof profile !== 'undefined' ? (profile.full_name || title || 'R').charAt(0).toUpperCase() : @js($initial)">{{ $initial }}</span>
                    </template>
                </div>

                @if ($name !== '')
                    <h1 class="resume-preview-name">{{ $name }}</h1>
                @endif

                @if ($headline !== '')
                    <p class="resume-preview-role">{{ $headline }}</p>
                @endif
            </header>

            @if ($contacts->isNotEmpty() || $socialLinks->isNotEmpty())
                <section class="resume-preview-sidebar-section">
                    <h2 class="resume-preview-sidebar-heading">Contact</h2>
                    <div class="resume-preview-contact-list">
                        @foreach ($contacts as $contact)
                            <p class="resume-preview-contact-row">
                                <x-ui.icon :name="$contact['icon']" class="resume-preview-contact-icon" />
                                <span>{{ $contact['display'] ?? $contact['value'] }}</span>
                            </p>
                        @endforeach
                        @foreach ($socialLinks as $link)
                            <p class="resume-preview-contact-row">
                                <x-ui.icon name="link" class="resume-preview-contact-icon" />
                                <span>{{ $hasText($link['label'] ?? null) ? $link['label'].': ' : '' }}{{ $displayUrl($link['url']) }}</span>
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('skills') && $skills->isNotEmpty())
                <section class="resume-preview-sidebar-section" style="order: {{ $sectionOrder('skills', 30) }}">
                    <h2 class="resume-preview-sidebar-heading">{{ $sectionTitle('skills', 'Skills') }}</h2>
                    <div class="resume-preview-skill-list">
                        @foreach ($skills as $skill)
                            <span class="resume-preview-skill">{{ $skill['name'] }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('languages') && $languages->isNotEmpty())
                <section class="resume-preview-sidebar-section" style="order: {{ $sectionOrder('languages', 40) }}">
                    <h2 class="resume-preview-sidebar-heading">{{ $sectionTitle('languages', 'Languages') }}</h2>
                    <div class="resume-preview-language-list">
                        @foreach ($languages as $language)
                            <p class="resume-preview-language-row">
                                <span>{{ $language['name'] }}</span>
                                @if ($hasText($language['proficiency'] ?? null))
                                    <span>{{ $language['proficiency'] }}</span>
                                @endif
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>

        <main class="resume-preview-main">
            @if ($sectionVisible('summary') && trim($summary) !== '')
                <section class="resume-preview-section" style="order: {{ $sectionOrder('summary', 0) }}">
                    <h2 class="resume-preview-section-heading"><span>{{ $sectionTitle('summary', 'Professional Summary') }}</span></h2>
                    <p class="resume-preview-copy">{{ $summary }}</p>
                </section>
            @endif

            @if ($sectionVisible('experience') && $experiences->isNotEmpty())
                <section class="resume-preview-section" style="order: {{ $sectionOrder('experience', 10) }}">
                    <h2 class="resume-preview-section-heading"><span>{{ $sectionTitle('experience', 'Experience') }}</span></h2>
                    <div class="resume-preview-entry-list">
                        @foreach ($experiences as $experience)
                            <article class="resume-preview-entry">
                                <div class="resume-preview-entry-head">
                                    <div>
                                        @if ($hasText($experience['position'] ?? null))
                                            <h3 class="resume-preview-entry-title">{{ $experience['position'] }}</h3>
                                        @endif
                                        @if ($hasText($experience['company'] ?? null) || $hasText($experience['location'] ?? null))
                                            <p class="resume-preview-entry-subtitle">
                                                {{ $experience['company'] ?? '' }}{{ $hasText($experience['company'] ?? null) && $hasText($experience['location'] ?? null) ? ' · ' : '' }}{{ $experience['location'] ?? '' }}
                                            </p>
                                        @endif
                                    </div>
                                    @if ($range = $this->formatDateRange($experience))
                                        <p class="resume-preview-entry-date">{{ $range }}</p>
                                    @endif
                                </div>
                                @if ($hasText($experience['description'] ?? null))
                                    <p class="resume-preview-entry-copy">{{ $experience['description'] }}</p>
                                @endif
                                @if ($splitTokens($experience['technologies'] ?? [])->isNotEmpty())
                                    <div class="resume-preview-tag-list">
                                        @foreach ($splitTokens($experience['technologies']) as $technology)
                                            <span>{{ $technology }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('education') && $educations->isNotEmpty())
                <section class="resume-preview-section" style="order: {{ $sectionOrder('education', 20) }}">
                    <h2 class="resume-preview-section-heading"><span>{{ $sectionTitle('education', 'Education') }}</span></h2>
                    <div class="resume-preview-entry-list">
                        @foreach ($educations as $education)
                            @php
                                $qualification = trim(implode(' · ', array_filter([
                                    $education['degree'] ?? null,
                                    $education['field_of_study'] ?? null,
                                ], $hasText)));
                            @endphp
                            <article class="resume-preview-entry">
                                <div class="resume-preview-entry-head">
                                    <div>
                                        @if ($qualification !== '')<h3 class="resume-preview-entry-title">{{ $qualification }}</h3>@endif
                                        <p class="resume-preview-entry-subtitle">{{ $education['institution'] ?? '' }}{{ $hasText($education['institution'] ?? null) && $hasText($education['location'] ?? null) ? ' · ' : '' }}{{ $education['location'] ?? '' }}</p>
                                    </div>
                                    @if ($range = $this->formatDateRange($education))
                                        <p class="resume-preview-entry-date">{{ $range }}</p>
                                    @endif
                                </div>
                                @if ($hasText($education['description'] ?? null))
                                    <p class="resume-preview-entry-copy">{{ $education['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('projects') && $projects->isNotEmpty())
                <section class="resume-preview-section" style="order: {{ $sectionOrder('projects', 30) }}">
                    <h2 class="resume-preview-section-heading"><span>{{ $sectionTitle('projects', 'Projects') }}</span></h2>
                    <div class="resume-preview-entry-list">
                        @foreach ($projects as $project)
                            <article class="resume-preview-entry">
                                <div class="resume-preview-entry-head">
                                    <div>
                                        <h3 class="resume-preview-entry-title">{{ $project['name'] }}</h3>
                                        @if ($hasText($project['role'] ?? null))<p class="resume-preview-entry-subtitle">{{ $project['role'] }}</p>@endif
                                    </div>
                                    @if ($range = $this->formatDateRange($project))
                                        <p class="resume-preview-entry-date">{{ $range }}</p>
                                    @endif
                                </div>
                                @if ($hasText($project['description'] ?? null))<p class="resume-preview-entry-copy">{{ $project['description'] }}</p>@endif
                                @if ($hasText($project['url'] ?? null) || $hasText($project['repository_url'] ?? null))
                                    <p class="resume-preview-link-line">
                                        @if ($hasText($project['url'] ?? null))<span>{{ $displayUrl($project['url']) }}</span>@endif
                                        @if ($hasText($project['url'] ?? null) && $hasText($project['repository_url'] ?? null))<span> · </span>@endif
                                        @if ($hasText($project['repository_url'] ?? null))<span>{{ $displayUrl($project['repository_url']) }}</span>@endif
                                    </p>
                                @endif
                                @if ($splitTokens($project['technologies'] ?? [])->isNotEmpty())
                                    <div class="resume-preview-tag-list">
                                        @foreach ($splitTokens($project['technologies']) as $technology)<span>{{ $technology }}</span>@endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('certifications') && $certifications->isNotEmpty())
                <section class="resume-preview-section" style="order: {{ $sectionOrder('certifications', 40) }}">
                    <h2 class="resume-preview-section-heading"><span>{{ $sectionTitle('certifications', 'Certifications') }}</span></h2>
                    <div class="resume-preview-entry-list resume-preview-entry-list-compact">
                        @foreach ($certifications as $certification)
                            <article class="resume-preview-entry">
                                <div class="resume-preview-entry-head">
                                    <div>
                                        <h3 class="resume-preview-entry-title">{{ $certification['name'] }}</h3>
                                        @if ($hasText($certification['issuer'] ?? null))<p class="resume-preview-entry-subtitle">{{ $certification['issuer'] }}</p>@endif
                                    </div>
                                    @if ($range = $this->formatDateRange($certification, 'issued_at', 'expires_at', '__never_current'))
                                        <p class="resume-preview-entry-date">{{ $range }}</p>
                                    @endif
                                </div>
                                @if ($hasText($certification['description'] ?? null))<p class="resume-preview-entry-copy">{{ $certification['description'] }}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('awards') && $awards->isNotEmpty())
                <section class="resume-preview-section" style="order: {{ $sectionOrder('awards', 50) }}">
                    <h2 class="resume-preview-section-heading"><span>{{ $sectionTitle('awards', 'Awards') }}</span></h2>
                    <div class="resume-preview-entry-list resume-preview-entry-list-compact">
                        @foreach ($awards as $award)
                            <article class="resume-preview-entry">
                                <div class="resume-preview-entry-head">
                                    <div><h3 class="resume-preview-entry-title">{{ $award['title'] }}</h3>@if ($hasText($award['issuer'] ?? null))<p class="resume-preview-entry-subtitle">{{ $award['issuer'] }}</p>@endif</div>
                                    @if ($date = $this->formatDate($award['awarded_at'] ?? null))<p class="resume-preview-entry-date">{{ $date }}</p>@endif
                                </div>
                                @if ($hasText($award['description'] ?? null))<p class="resume-preview-entry-copy">{{ $award['description'] }}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('references') && $references->isNotEmpty())
                <section class="resume-preview-section" style="order: {{ $sectionOrder('references', 60) }}">
                    <h2 class="resume-preview-section-heading"><span>{{ $sectionTitle('references', 'References') }}</span></h2>
                    <div class="resume-preview-reference-grid">
                        @foreach ($references as $reference)
                            <article class="resume-preview-entry">
                                @if ($hasText($reference['name'] ?? null))<h3 class="resume-preview-entry-title">{{ $reference['name'] }}</h3>@endif
                                @if ($hasText($reference['title'] ?? null) || $hasText($reference['company'] ?? null))
                                    <p class="resume-preview-entry-subtitle">{{ $reference['title'] ?? '' }}{{ $hasText($reference['title'] ?? null) && $hasText($reference['company'] ?? null) ? ' · ' : '' }}{{ $reference['company'] ?? '' }}</p>
                                @elseif (! empty($reference['available_on_request']))
                                    <p class="resume-preview-entry-subtitle">Available on request</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($sectionVisible('custom_sections'))
                @foreach ($customSections as $customIndex => $customSection)
                    @php
                        $customItems = $rows($customSection['items'] ?? [])->filter(fn ($item) => $hasText($item['title'] ?? null) || $hasText($item['description'] ?? null))->values();
                    @endphp
                    @if ($hasText($customSection['description'] ?? null) || $customItems->isNotEmpty())
                        <section class="resume-preview-section" style="order: {{ $sectionOrder('custom_sections', 70) + $customIndex }}">
                            <h2 class="resume-preview-section-heading"><span>{{ $customSection['title'] }}</span></h2>
                            @if ($hasText($customSection['description'] ?? null))<p class="resume-preview-copy">{{ $customSection['description'] }}</p>@endif
                            @if ($customItems->isNotEmpty())
                                <div class="resume-preview-entry-list">
                                    @foreach ($customItems as $item)
                                        <article class="resume-preview-entry">
                                            <div class="resume-preview-entry-head">
                                                <div>
                                                    @if ($hasText($item['title'] ?? null))<h3 class="resume-preview-entry-title">{{ $item['title'] }}</h3>@endif
                                                    @if ($hasText($item['subtitle'] ?? null))<p class="resume-preview-entry-subtitle">{{ $item['subtitle'] }}</p>@endif
                                                </div>
                                                @if ($range = $this->formatDateRange($item, 'start_date', 'end_date', '__never_current'))<p class="resume-preview-entry-date">{{ $range }}</p>@endif
                                            </div>
                                            @if ($hasText($item['description'] ?? null))<p class="resume-preview-entry-copy">{{ $item['description'] }}</p>@endif
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                @endforeach
            @endif
        </main>
    </div>
</div>

<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Resume;
use App\Models\ResumeSection;
use App\Models\ResumeShare;
use App\Models\ResumeVersion;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ResumeService
{
    public function __construct(private readonly ResumeVersionService $versions) {}

    public function create(User $user, array $data): Resume
    {
        return DB::transaction(function () use ($user, $data): Resume {
            $data = $this->withDefaultSections($data);

            $resume = $user->resumes()->create([
                'uuid' => (string) Str::uuid(),
                'template_id' => $data['template_id'] ?? null,
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($user, $data['title']),
                'status' => 'draft',
                'source' => $data['source'] ?? 'builder',
                'target_role' => $data['target_role'] ?? null,
                'target_company' => $data['target_company'] ?? null,
                'language' => $data['language'] ?? 'en',
                'settings' => $this->settingsFromData($data),
            ]);

            $this->syncContent($resume, $data);
            $resume = $this->persistDerivedFields($resume);
            $this->versions->create($resume, $user, 'created', 'Initial draft');

            return $resume;
        });
    }

    public function update(Resume $resume, User $user, array $data, string $reason = 'manual'): Resume
    {
        return DB::transaction(function () use ($resume, $user, $data, $reason): Resume {
            $incomingTemplateId = $data['template_id'] ?? $resume->template_id;
            $previousTemplateId = $resume->template_id;
            $currentSettings   = $resume->settings ?? [];

            // When the user is switching templates, snapshot the current theme under the
            // old template key so it can be restored if they switch back.
            if ((string) $incomingTemplateId !== (string) $previousTemplateId && $previousTemplateId) {
                $oldThemeKey = 'template_themes.' . $previousTemplateId;
                $currentTheme = $currentSettings['theme'] ?? [];
                data_set($currentSettings, $oldThemeKey, $currentTheme);
            }

            $newSettings = $this->settingsFromData($data);

            // When switching TO a new template, restore any previously-saved theme for it,
            // but only when the autosaved payload does NOT already carry a real user theme.
            if ((string) $incomingTemplateId !== (string) $previousTemplateId && $incomingTemplateId) {
                $savedThemeForNew = data_get($currentSettings, 'template_themes.' . $incomingTemplateId);
                if ($savedThemeForNew && empty($newSettings['theme'])) {
                    $newSettings['theme'] = $savedThemeForNew;
                }
            }

            $mergedSettings = array_replace_recursive($currentSettings, $newSettings);

            $resume->fill([
                'template_id'   => $incomingTemplateId,
                'title'         => $data['title'] ?? $resume->title,
                'slug'          => isset($data['title']) && $data['title'] !== $resume->title
                    ? $this->uniqueSlug($resume->user, $data['title'], $resume->id)
                    : $resume->slug,
                'target_role'   => $data['target_role'] ?? null,
                'target_company' => $data['target_company'] ?? null,
                'language'      => $data['language'] ?? $resume->language,
                'settings'      => $mergedSettings,
            ]);

            if ($reason === 'autosave') {
                $resume->last_autosaved_at = now();
            }

            $resume->save();
            $this->syncContent($resume, $data);
            $resume = $this->persistDerivedFields($resume);

            if ($reason !== 'autosave') {
                $this->versions->create($resume, $user, $reason, Str::headline($reason));
            }

            return $resume;
        });
    }

    /**
     * Return the stored theme for a specific template on this resume.
     * Falls back to the current active theme if no per-template snapshot exists.
     */
    public function getThemeForTemplate(Resume $resume, int|string $templateId): array
    {
        $settings = $resume->settings ?? [];
        $perTemplate = data_get($settings, 'template_themes.' . $templateId);
        if ($perTemplate && is_array($perTemplate)) {
            return $perTemplate;
        }
        // No snapshot yet — return a clean default so no edits bleed from another template
        return $this->theme([]);
    }

    public function duplicate(Resume $resume, User $user): Resume
    {
        return DB::transaction(function () use ($resume, $user): Resume {
            $resume->loadMissing($this->versions->snapshotRelations());

            $copy = $resume->replicate(['uuid', 'slug', 'last_autosaved_at', 'last_exported_at', 'archived_at']);
            $copy->uuid = (string) Str::uuid();
            $copy->user_id = $user->id;
            $copy->title = $resume->title.' Copy';
            $copy->slug = $this->uniqueSlug($user, $copy->title);
            $copy->is_archived = false;
            $copy->is_favorite = false;
            $copy->save();

            $this->copyHasOne($resume, $copy, 'profile');
            $this->copyHasOne($resume, $copy, 'summary');
            $this->copyRows($resume, $copy, 'socialLinks');
            $this->copyRows($resume, $copy, 'experiences');
            $this->copyRows($resume, $copy, 'educations');
            $this->copyRows($resume, $copy, 'projects');
            $this->copyRows($resume, $copy, 'certifications');
            $this->copyRows($resume, $copy, 'awards');
            $this->copyRows($resume, $copy, 'references');
            $this->copyRows($resume, $copy, 'sections');
            $this->copyCustomSections($resume, $copy);
            $this->copyTaxonomy($resume, $copy, 'skills', ['category', 'proficiency', 'years_experience', 'is_visible', 'sort_order']);
            $this->copyTaxonomy($resume, $copy, 'languages', ['proficiency', 'is_visible', 'sort_order']);
            $copy = $this->persistDerivedFields($copy);
            $this->versions->create($copy, $user, 'duplicated', 'Copied from '.$resume->title);

            return $copy;
        });
    }

    public function restoreVersion(Resume $resume, ResumeVersion $version, User $user): Resume
    {
        if ($version->resume_id !== $resume->id) {
            throw new InvalidArgumentException('The selected version does not belong to this resume.');
        }

        return $this->update($resume, $user, $this->payloadFromSnapshot($version->snapshot ?? []), 'restored');
    }

    public function setArchived(Resume $resume, bool $archived): Resume
    {
        $resume->forceFill([
            'is_archived' => $archived,
            'archived_at' => $archived ? now() : null,
        ])->save();

        return $resume;
    }

    public function toggleFavorite(Resume $resume): Resume
    {
        $resume->forceFill(['is_favorite' => ! $resume->is_favorite])->save();

        return $resume;
    }

    public function share(Resume $resume, array $data): ResumeShare
    {
        return $resume->shares()->create([
            'token' => Str::random(64),
            'slug' => $resume->slug.'-'.Str::lower(Str::random(6)),
            'visibility' => $data['visibility'],
            'password_hash' => filled($data['password'] ?? null) ? Hash::make($data['password']) : null,
            'allow_download' => (bool) ($data['allow_download'] ?? false),
            'expires_at' => $data['expires_at'] ?? null,
            'metadata' => ['created_from' => 'app'],
        ]);
    }

    public function recordDownload(Resume $resume, User $user, string $format, ?string $path = null): void
    {
        $resume->downloads()->create([
            'user_id' => $user->id,
            'format' => $format,
            'file_path' => $path,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'downloaded_at' => now(),
        ]);

        $resume->forceFill(['last_exported_at' => now()])->save();
    }

    public function plainText(Resume $resume): string
    {
        $resume->loadMissing($this->versions->snapshotRelations());
        $settings = $resume->settings ?? [];
        $summary = $resume->summary?->content ?: ($settings['summary'] ?? null);
        $chunks = [
            $resume->title,
            $resume->target_role,
            $resume->target_company,
            $resume->profile?->full_name,
            $resume->profile?->headline,
            $resume->profile?->email,
            $resume->profile?->phone,
            $resume->profile?->location,
            $resume->profile?->website,
        ];

        foreach ($resume->socialLinks->where('is_visible', true) as $link) {
            $chunks[] = trim($link->label.' '.$link->url);
        }

        if ($this->sectionVisible($resume, 'summary')) {
            $chunks[] = 'Summary '.$summary;
        }

        if ($this->sectionVisible($resume, 'experience')) {
            $chunks[] = 'Experience';
            foreach ($resume->experiences->where('is_visible', true) as $experience) {
                $chunks[] = trim($experience->position.' '.$experience->company.' '.$experience->location.' '.$experience->description);
                $chunks[] = implode(', ', $experience->achievements ?? []);
                $chunks[] = implode(', ', $experience->technologies ?? []);
            }
        }

        if ($this->sectionVisible($resume, 'education')) {
            $chunks[] = 'Education';
            foreach ($resume->educations->where('is_visible', true) as $education) {
                $chunks[] = trim($education->institution.' '.$education->degree.' '.$education->field_of_study.' '.$education->grade.' '.$education->description);
            }
        }

        if ($this->sectionVisible($resume, 'projects')) {
            $chunks[] = 'Projects';
            foreach ($resume->projects->where('is_visible', true) as $project) {
                $chunks[] = trim($project->name.' '.$project->role.' '.$project->description.' '.implode(', ', $project->technologies ?? []));
            }
        }

        if ($this->sectionVisible($resume, 'skills')) {
            $chunks[] = 'Skills '.implode(', ', $settings['skills'] ?? $resume->skills->pluck('name')->all());
        }

        if ($this->sectionVisible($resume, 'languages')) {
            $chunks[] = 'Languages '.implode(', ', $settings['languages'] ?? $resume->languages->pluck('name')->all());
        }

        if ($this->sectionVisible($resume, 'certifications')) {
            $chunks[] = 'Certifications';
            foreach ($resume->certifications->where('is_visible', true) as $certification) {
                $chunks[] = trim($certification->name.' '.$certification->issuer.' '.$certification->description);
            }
        }

        if ($this->sectionVisible($resume, 'awards')) {
            $chunks[] = 'Awards';
            foreach ($resume->awards->where('is_visible', true) as $award) {
                $chunks[] = trim($award->title.' '.$award->issuer.' '.$award->description);
            }
        }

        if ($this->sectionVisible($resume, 'references')) {
            $chunks[] = 'References';
            foreach ($resume->references->where('is_visible', true) as $reference) {
                $chunks[] = trim($reference->name.' '.$reference->title.' '.$reference->company.' '.$reference->email.' '.$reference->phone);
            }
        }

        if ($this->sectionVisible($resume, 'custom_sections')) {
            foreach ($resume->customSections->where('is_visible', true) as $section) {
                $chunks[] = $section->title;
                $chunks[] = $section->description;
                foreach ($section->items->where('is_visible', true) as $item) {
                    $chunks[] = trim($item->title.' '.$item->subtitle.' '.$item->description);
                }
            }
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($chunks))));
    }

    public function recentFor(User $user, int $limit = 5): Collection
    {
        return $user->resumes()->with(['template', 'profile'])->latest('updated_at')->limit($limit)->get();
    }

    private function syncContent(Resume $resume, array $data): void
    {
        if (array_key_exists('profile', $data)) {
            $this->syncProfile($resume, $data['profile'] ?? []);
        }

        if (array_key_exists('summary', $data)) {
            $this->syncSummary($resume, $data['summary'] ?? null);
        }

        if (array_key_exists('social_links', $data)) {
            $this->syncSocialLinks($resume, $data['social_links'] ?? []);
        }

        if (array_key_exists('experiences', $data)) {
            $this->syncExperiences($resume, $data['experiences'] ?? []);
        }

        if (array_key_exists('educations', $data)) {
            $this->syncEducations($resume, $data['educations'] ?? []);
        }

        if (array_key_exists('projects', $data)) {
            $this->syncProjects($resume, $data['projects'] ?? []);
        }

        if (array_key_exists('skills', $data)) {
            $this->syncSkills($resume, $data['skills'] ?? []);
        }

        if (array_key_exists('languages', $data)) {
            $this->syncLanguages($resume, $data['languages'] ?? []);
        }

        if (array_key_exists('certifications', $data)) {
            $this->syncCertifications($resume, $data['certifications'] ?? []);
        }

        if (array_key_exists('awards', $data)) {
            $this->syncAwards($resume, $data['awards'] ?? []);
        }

        if (array_key_exists('references', $data)) {
            $this->syncReferences($resume, $data['references'] ?? []);
        }

        if (array_key_exists('custom_sections', $data)) {
            $this->syncCustomSections($resume, $data['custom_sections'] ?? []);
        }

        if (array_key_exists('sections', $data)) {
            $this->syncSections($resume, $data['sections'] ?? []);
        }
    }

    private function syncProfile(Resume $resume, array $profile): void
    {
        $profileData = collect(Arr::only($profile, [
            'full_name', 'headline', 'email', 'phone', 'website', 'location',
            'city', 'state', 'country', 'postal_code',
        ]))->map(fn ($value) => filled($value) ? $value : null)->all();

        if (filled($profile['photo_path'] ?? null)) {
            $profileData['photo_path'] = $profile['photo_path'];
        }

        if (array_key_exists('metadata', $profile)) {
            $profileData['metadata'] = $profile['metadata'] ?? [];
        }

        $resume->profile()->updateOrCreate(['resume_id' => $resume->id], $profileData);
    }

    private function syncSummary(Resume $resume, ?string $summary): void
    {
        $resume->summary()->updateOrCreate(['resume_id' => $resume->id], [
            'content' => $summary,
            'word_count' => str_word_count((string) $summary),
            'metadata' => [],
        ]);
    }

    private function syncSocialLinks(Resume $resume, array $links): void
    {
        $this->syncRows($resume, 'socialLinks', $links, fn (array $link): bool => filled($link['url'] ?? null), fn (array $link, int $index): array => [
            'platform' => $link['platform'] ?? 'website',
            'label' => $link['label'] ?? null,
            'url' => $link['url'],
            'is_visible' => $this->bool($link['is_visible'] ?? true),
            'sort_order' => (int) ($link['sort_order'] ?? $index),
        ]);
    }

    private function syncExperiences(Resume $resume, array $experiences): void
    {
        $this->syncRows($resume, 'experiences', $experiences, fn (array $experience): bool => filled($experience['company'] ?? null) || filled($experience['position'] ?? null), fn (array $experience, int $index): array => [
            'company' => $experience['company'] ?? 'Independent',
            'position' => $experience['position'] ?? 'Contributor',
            'employment_type' => $experience['employment_type'] ?? null,
            'location' => $experience['location'] ?? null,
            'start_date' => $experience['start_date'] ?? null,
            'end_date' => $experience['end_date'] ?? null,
            'is_current' => $this->bool($experience['is_current'] ?? false),
            'description' => $experience['description'] ?? null,
            'achievements' => $experience['achievements'] ?? $this->lines($experience['description'] ?? ''),
            'technologies' => $this->list($experience['technologies'] ?? []),
            'is_visible' => $this->bool($experience['is_visible'] ?? true),
            'sort_order' => (int) ($experience['sort_order'] ?? $index),
        ]);
    }

    private function syncEducations(Resume $resume, array $educations): void
    {
        $this->syncRows($resume, 'educations', $educations, fn (array $education): bool => filled($education['institution'] ?? null), fn (array $education, int $index): array => [
            'institution' => $education['institution'],
            'degree' => $education['degree'] ?? null,
            'field_of_study' => $education['field_of_study'] ?? null,
            'location' => $education['location'] ?? null,
            'start_date' => $education['start_date'] ?? null,
            'end_date' => $education['end_date'] ?? null,
            'is_current' => $this->bool($education['is_current'] ?? false),
            'grade' => $education['grade'] ?? null,
            'description' => $education['description'] ?? null,
            'highlights' => $education['highlights'] ?? $this->lines($education['description'] ?? ''),
            'is_visible' => $this->bool($education['is_visible'] ?? true),
            'sort_order' => (int) ($education['sort_order'] ?? $index),
            'metadata' => [],
        ]);
    }

    private function syncProjects(Resume $resume, array $projects): void
    {
        $this->syncRows($resume, 'projects', $projects, fn (array $project): bool => filled($project['name'] ?? null), fn (array $project, int $index): array => [
            'name' => $project['name'],
            'role' => $project['role'] ?? null,
            'url' => $project['url'] ?? null,
            'repository_url' => $project['repository_url'] ?? null,
            'start_date' => $project['start_date'] ?? null,
            'end_date' => $project['end_date'] ?? null,
            'is_current' => $this->bool($project['is_current'] ?? false),
            'description' => $project['description'] ?? null,
            'highlights' => $project['highlights'] ?? $this->lines($project['description'] ?? ''),
            'technologies' => $this->list($project['technologies'] ?? []),
            'is_visible' => $this->bool($project['is_visible'] ?? true),
            'sort_order' => (int) ($project['sort_order'] ?? $index),
            'metadata' => [],
        ]);
    }

    private function syncSkills(Resume $resume, mixed $skills): void
    {
        $sync = [];

        foreach ($this->skillRows($skills) as $index => $row) {
            $skill = $this->findOrCreateSkill($row['name'], $row['category'] ?? null);
            $sync[$skill->id] = [
                'category' => $row['category'] ?? null,
                'proficiency' => $row['proficiency'] ?? null,
                'years_experience' => $row['years_experience'] ?? null,
                'is_visible' => $this->bool($row['is_visible'] ?? true),
                'sort_order' => (int) ($row['sort_order'] ?? $index),
            ];
        }

        $resume->skills()->sync($sync);
    }

    private function syncLanguages(Resume $resume, mixed $languages): void
    {
        $sync = [];

        foreach ($this->languageRows($languages) as $index => $row) {
            $language = $this->findOrCreateLanguage($row['name'], $row['iso_code'] ?? null);
            $sync[$language->id] = [
                'proficiency' => $row['proficiency'] ?? null,
                'is_visible' => $this->bool($row['is_visible'] ?? true),
                'sort_order' => (int) ($row['sort_order'] ?? $index),
            ];
        }

        $resume->languages()->sync($sync);
    }

    private function syncCertifications(Resume $resume, array $certifications): void
    {
        $this->syncRows($resume, 'certifications', $certifications, fn (array $certification): bool => filled($certification['name'] ?? null), fn (array $certification, int $index): array => [
            'name' => $certification['name'],
            'issuer' => $certification['issuer'] ?? null,
            'issued_at' => $certification['issued_at'] ?? null,
            'expires_at' => $certification['expires_at'] ?? null,
            'credential_id' => $certification['credential_id'] ?? null,
            'credential_url' => $certification['credential_url'] ?? null,
            'description' => $certification['description'] ?? null,
            'is_visible' => $this->bool($certification['is_visible'] ?? true),
            'sort_order' => (int) ($certification['sort_order'] ?? $index),
            'metadata' => [],
        ]);
    }

    private function syncAwards(Resume $resume, array $awards): void
    {
        $this->syncRows($resume, 'awards', $awards, fn (array $award): bool => filled($award['title'] ?? null), fn (array $award, int $index): array => [
            'title' => $award['title'],
            'issuer' => $award['issuer'] ?? null,
            'awarded_at' => $award['awarded_at'] ?? null,
            'description' => $award['description'] ?? null,
            'is_visible' => $this->bool($award['is_visible'] ?? true),
            'sort_order' => (int) ($award['sort_order'] ?? $index),
            'metadata' => [],
        ]);
    }

    private function syncReferences(Resume $resume, array $references): void
    {
        $this->syncRows($resume, 'references', $references, fn (array $reference): bool => filled($reference['name'] ?? null) || $this->bool($reference['available_on_request'] ?? false), fn (array $reference, int $index): array => [
            'name' => $reference['name'] ?? 'Available on request',
            'title' => $reference['title'] ?? null,
            'company' => $reference['company'] ?? null,
            'email' => $reference['email'] ?? null,
            'phone' => $reference['phone'] ?? null,
            'relationship' => $reference['relationship'] ?? null,
            'available_on_request' => $this->bool($reference['available_on_request'] ?? false),
            'is_visible' => $this->bool($reference['is_visible'] ?? true),
            'sort_order' => (int) ($reference['sort_order'] ?? $index),
            'metadata' => [],
        ]);
    }

    private function syncCustomSections(Resume $resume, array $sections): void
    {
        foreach ($resume->customSections as $section) {
            $section->items()->delete();
        }

        $resume->customSections()->delete();

        foreach (array_values($sections) as $index => $section) {
            if (! filled($section['title'] ?? null)) {
                continue;
            }

            $customSection = $resume->customSections()->create([
                'title' => $section['title'],
                'description' => $section['description'] ?? null,
                'is_visible' => $this->bool($section['is_visible'] ?? true),
                'sort_order' => (int) ($section['sort_order'] ?? $index),
                'settings' => $section['settings'] ?? [],
            ]);

            foreach (array_values($section['items'] ?? []) as $itemIndex => $item) {
                if (! filled($item['title'] ?? null) && ! filled($item['description'] ?? null)) {
                    continue;
                }

                $customSection->items()->create([
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['subtitle'] ?? null,
                    'url' => $item['url'] ?? null,
                    'start_date' => $item['start_date'] ?? null,
                    'end_date' => $item['end_date'] ?? null,
                    'description' => $item['description'] ?? null,
                    'fields' => $item['fields'] ?? [],
                    'is_visible' => $this->bool($item['is_visible'] ?? true),
                    'sort_order' => (int) ($item['sort_order'] ?? $itemIndex),
                ]);
            }
        }
    }

    private function syncSections(Resume $resume, array $sections): void
    {
        $orderedSections = collect($sections)
            ->filter(fn ($section) => filled($section['section_key'] ?? null))
            ->values();

        if ($orderedSections->isEmpty()) {
            $orderedSections = collect($this->defaultSections());
        }

        $now = now();
        $rows = $orderedSections->map(fn (array $section, int $index): array => [
            'resume_id' => $resume->id,
            'section_key' => $section['section_key'],
            'title' => $section['title'] ?? Str::of($section['section_key'])->replace('_', ' ')->replace('-', ' ')->title()->value(),
            'is_visible' => $this->bool($section['is_visible'] ?? true),
            'sort_order' => (int) ($section['sort_order'] ?? $index),
            'settings' => json_encode($section['settings'] ?? []),
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        ResumeSection::query()->upsert(
            $rows,
            ['resume_id', 'section_key'],
            ['title', 'is_visible', 'sort_order', 'settings', 'updated_at'],
        );

        $resume->sections()->whereNotIn('section_key', $orderedSections->pluck('section_key')->all())->delete();
    }

    private function syncRows(Resume $resume, string $relation, array $items, callable $shouldPersist, callable $map): void
    {
        $existing = $resume->{$relation}()->get()->keyBy('id');
        $retainedIds = [];

        foreach (array_values($items) as $index => $item) {
            $item = is_array($item) ? $item : [];

            if (! $shouldPersist($item)) {
                continue;
            }

            $attributes = $map($item, $index);
            $id = isset($item['id']) ? (int) $item['id'] : null;
            $model = $id ? $existing->get($id) : null;

            if ($model) {
                $model->fill($attributes);
                if ($model->isDirty()) {
                    $model->save();
                }
                $retainedIds[] = $model->getKey();
            } else {
                $retainedIds[] = $resume->{$relation}()->create($attributes)->getKey();
            }
        }

        $staleIds = $existing->keys()->diff($retainedIds);
        if ($staleIds->isNotEmpty()) {
            $resume->{$relation}()->whereKey($staleIds->all())->delete();
        }
    }

    private function settingsFromData(array $data): array
    {
        $settings = [];

        if (array_key_exists('summary', $data)) {
            $settings['summary'] = $data['summary'];
        }
        if (array_key_exists('skills', $data)) {
            $settings['skills'] = collect($this->skillRows($data['skills'] ?? []))->pluck('name')->values()->all();
        }
        if (array_key_exists('languages', $data)) {
            $settings['languages'] = collect($this->languageRows($data['languages'] ?? []))->pluck('name')->values()->all();
        }
        if (array_key_exists('sections', $data)) {
            $settings['sections'] = $this->normalizeSections($data['sections'] ?? []);
        }
        if (array_key_exists('theme', $data)) {
            $settings['theme'] = $this->theme($data['theme'] ?? []);
        }
        if (array_key_exists('import', $data)) {
            $settings['import'] = $data['import'];
        }

        return $settings;
    }

    private function persistDerivedFields(Resume $resume): Resume
    {
        $resume = $this->freshResume($resume);

        $resume->forceFill([
            'completion_score' => $this->completionScore($resume),
            'search_text' => $this->plainText($resume),
        ])->save();

        return $resume;
    }

    private function completionScore(Resume $resume): int
    {
        $resume->loadMissing($this->versions->snapshotRelations());
        $settings = $resume->settings ?? [];
        $score = 0;

        $score += $resume->profile?->full_name ? 8 : 0;
        $score += $resume->profile?->email ? 8 : 0;
        $score += $resume->profile?->phone || $resume->profile?->website ? 6 : 0;
        $score += $resume->profile?->headline ? 8 : 0;
        $score += filled($resume->summary?->content ?: ($settings['summary'] ?? null)) ? 15 : 0;
        $score += $resume->experiences->where('is_visible', true)->isNotEmpty() ? 20 : 0;
        $score += $resume->educations->where('is_visible', true)->isNotEmpty() ? 10 : 0;
        $score += count($settings['skills'] ?? []) >= 4 ? 12 : 0;
        $score += $resume->projects->where('is_visible', true)->isNotEmpty() ? 5 : 0;
        $score += $resume->languages->isNotEmpty() || $resume->certifications->where('is_visible', true)->isNotEmpty() ? 4 : 0;
        $score += $resume->sections->isNotEmpty() ? 4 : 0;

        return min(100, $score);
    }

    private function payloadFromSnapshot(array $snapshot): array
    {
        $resume = $snapshot['resume'] ?? [];
        $settings = $resume['settings'] ?? [];

        return [
            'title' => $resume['title'] ?? 'Restored Resume',
            'target_role' => $resume['target_role'] ?? null,
            'target_company' => $resume['target_company'] ?? null,
            'template_id' => $resume['template_id'] ?? null,
            'language' => $resume['language'] ?? 'en',
            'summary' => $snapshot['summary']['content'] ?? ($settings['summary'] ?? null),
            'theme' => $settings['theme'] ?? [],
            'profile' => $this->snapshotRow($snapshot['profile'] ?? []),
            'social_links' => $this->snapshotRows($snapshot['social_links'] ?? []),
            'experiences' => $this->snapshotRows($snapshot['experiences'] ?? []),
            'educations' => $this->snapshotRows($snapshot['educations'] ?? []),
            'projects' => $this->snapshotRows($snapshot['projects'] ?? []),
            'skills' => collect($snapshot['skills'] ?? [])->map(fn (array $skill): array => [
                'name' => $skill['name'] ?? '',
                'category' => $skill['pivot']['category'] ?? ($skill['category'] ?? null),
                'proficiency' => $skill['pivot']['proficiency'] ?? null,
                'years_experience' => $skill['pivot']['years_experience'] ?? null,
                'is_visible' => $skill['pivot']['is_visible'] ?? true,
                'sort_order' => $skill['pivot']['sort_order'] ?? 0,
            ])->all(),
            'languages' => collect($snapshot['languages'] ?? [])->map(fn (array $language): array => [
                'name' => $language['name'] ?? '',
                'iso_code' => $language['iso_code'] ?? null,
                'proficiency' => $language['pivot']['proficiency'] ?? null,
                'is_visible' => $language['pivot']['is_visible'] ?? true,
                'sort_order' => $language['pivot']['sort_order'] ?? 0,
            ])->all(),
            'certifications' => $this->snapshotRows($snapshot['certifications'] ?? []),
            'awards' => $this->snapshotRows($snapshot['awards'] ?? []),
            'references' => $this->snapshotRows($snapshot['references'] ?? []),
            'custom_sections' => collect($snapshot['custom_sections'] ?? [])->map(function (array $section): array {
                $section = $this->snapshotRow($section);
                $section['items'] = $this->snapshotRows($section['items'] ?? []);

                return $section;
            })->all(),
            'sections' => $this->snapshotRows($snapshot['sections'] ?? []),
        ];
    }

    private function snapshotRows(array $rows): array
    {
        return collect($rows)->map(fn (array $row): array => $this->snapshotRow($row))->values()->all();
    }

    private function snapshotRow(?array $row): array
    {
        return Arr::except($row ?? [], ['id', 'resume_id', 'created_at', 'updated_at', 'deleted_at', 'pivot']);
    }

    private function freshResume(Resume $resume): Resume
    {
        return $resume->refresh()->load($this->versions->snapshotRelations());
    }

    private function withDefaultSections(array $data): array
    {
        if (! array_key_exists('sections', $data) || $data['sections'] === []) {
            $data['sections'] = $this->defaultSections();
        }

        return $data;
    }

    private function defaultSections(): array
    {
        return [
            ['section_key' => 'summary', 'title' => 'Profile', 'is_visible' => true, 'sort_order' => 0],
            ['section_key' => 'experience', 'title' => 'Experience', 'is_visible' => true, 'sort_order' => 1],
            ['section_key' => 'education', 'title' => 'Education', 'is_visible' => true, 'sort_order' => 2],
            ['section_key' => 'skills', 'title' => 'Skills', 'is_visible' => true, 'sort_order' => 3],
            ['section_key' => 'projects', 'title' => 'Projects', 'is_visible' => true, 'sort_order' => 4],
            ['section_key' => 'languages', 'title' => 'Languages', 'is_visible' => true, 'sort_order' => 5],
            ['section_key' => 'certifications', 'title' => 'Certifications', 'is_visible' => true, 'sort_order' => 6],
            ['section_key' => 'awards', 'title' => 'Awards', 'is_visible' => true, 'sort_order' => 7],
            ['section_key' => 'references', 'title' => 'References', 'is_visible' => false, 'sort_order' => 8],
            ['section_key' => 'custom_sections', 'title' => 'Custom Sections', 'is_visible' => true, 'sort_order' => 9],
        ];
    }

    private function normalizeSections(array $sections): array
    {
        return collect($sections ?: $this->defaultSections())
            ->filter(fn ($section) => filled($section['section_key'] ?? null))
            ->values()
            ->map(fn ($section, int $index): array => [
                'section_key' => $section['section_key'],
                'title' => $section['title'] ?? Str::headline($section['section_key']),
                'is_visible' => $this->bool($section['is_visible'] ?? true),
                'sort_order' => (int) ($section['sort_order'] ?? $index),
                'settings' => $section['settings'] ?? [],
            ])
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    private function sectionVisible(Resume $resume, string $key): bool
    {
        $section = $resume->sections->firstWhere('section_key', $key);

        return ! $section || $section->is_visible;
    }

    private function theme(array $theme): array
    {
        // Build the validated base — only sanitize known numeric/enum fields.
        // We keep ALL other user-supplied keys (header_color, header_scale, styles, etc.)
        // so that custom per-element overrides are never silently dropped on save.
        $base = [
            'accent_color'   => $theme['accent_color'] ?? '#3525cd',
            'secondary_color' => $theme['secondary_color'] ?? '#142845',
            'font_pairing'   => $theme['font_pairing'] ?? 'modern',
            'heading_font'   => $theme['heading_font'] ?? 'Poppins',
            'body_font'      => $theme['body_font'] ?? 'Inter',
            'font_scale'     => max(80, min(125, (int) ($theme['font_scale'] ?? 100))),
            'density'        => $theme['density'] ?? 'balanced',
            'page_size'      => $theme['page_size'] ?? 'a4',
            'layout'         => $theme['layout'] ?? 'two-column',
            'sidebar_width'  => max(28, min(42, (int) ($theme['sidebar_width'] ?? 34))),
            'photo_position' => $theme['photo_position'] ?? 'center',
            'section_spacing' => $theme['section_spacing'] ?? 'medium',
            'content_width'  => $theme['content_width'] ?? 'standard',
            'page_background' => $theme['page_background'] ?? '#ffffff',
            'dividers'       => $this->bool($theme['dividers'] ?? true),
            'shadow'         => $this->bool($theme['shadow'] ?? true),
        ];

        // Preserve any extra keys the editor stores (header_color, header_scale, styles map, etc.)
        $knownKeys = array_keys($base);
        foreach ($theme as $key => $value) {
            if (! in_array($key, $knownKeys, true)) {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    private function skillRows(mixed $skills): array
    {
        if (is_string($skills)) {
            $skills = preg_split('/[\n,]+/', $skills) ?: [];
        }

        return collect($skills)
            ->map(function ($skill, int $index): array {
                if (is_array($skill)) {
                    return [
                        'name' => trim((string) ($skill['name'] ?? '')),
                        'category' => $skill['category'] ?? null,
                        'proficiency' => $skill['proficiency'] ?? null,
                        'years_experience' => $skill['years_experience'] ?? null,
                        'is_visible' => $skill['is_visible'] ?? true,
                        'sort_order' => $skill['sort_order'] ?? $index,
                    ];
                }

                return [
                    'name' => trim((string) $skill),
                    'is_visible' => true,
                    'sort_order' => $index,
                ];
            })
            ->filter(fn (array $skill): bool => filled($skill['name']))
            ->unique(fn (array $skill): string => Str::lower($skill['name']))
            ->values()
            ->all();
    }

    private function languageRows(mixed $languages): array
    {
        if (is_string($languages)) {
            $languages = preg_split('/[\n,]+/', $languages) ?: [];
        }

        return collect($languages)
            ->map(function ($language, int $index): array {
                if (is_array($language)) {
                    return [
                        'name' => trim((string) ($language['name'] ?? '')),
                        'iso_code' => $language['iso_code'] ?? null,
                        'proficiency' => $language['proficiency'] ?? null,
                        'is_visible' => $language['is_visible'] ?? true,
                        'sort_order' => $language['sort_order'] ?? $index,
                    ];
                }

                return [
                    'name' => trim((string) $language),
                    'is_visible' => true,
                    'sort_order' => $index,
                ];
            })
            ->filter(fn (array $language): bool => filled($language['name']))
            ->unique(fn (array $language): string => Str::lower($language['name']))
            ->values()
            ->all();
    }

    private function findOrCreateSkill(string $name, ?string $category): Skill
    {
        $slug = Str::slug($name) ?: Str::random(8);
        $skill = Skill::withTrashed()
            ->where('slug', $slug)
            ->orWhere('name', $name)
            ->first();

        if (! $skill) {
            return Skill::query()->create([
                'name' => $name,
                'slug' => $slug,
                'category' => $category,
            ]);
        }

        if ($skill->trashed()) {
            $skill->restore();
        }

        if ($category && ! $skill->category) {
            $skill->forceFill(['category' => $category])->save();
        }

        return $skill;
    }

    private function findOrCreateLanguage(string $name, ?string $isoCode): Language
    {
        $language = Language::withTrashed()
            ->where('name', $name)
            ->when($isoCode, fn ($query) => $query->orWhere('iso_code', $isoCode))
            ->first();

        if (! $language) {
            return Language::query()->create([
                'name' => $name,
                'iso_code' => $isoCode,
            ]);
        }

        if ($language->trashed()) {
            $language->restore();
        }

        if ($isoCode && ! $language->iso_code) {
            $language->forceFill(['iso_code' => $isoCode])->save();
        }

        return $language;
    }

    private function copyHasOne(Resume $resume, Resume $copy, string $relation): void
    {
        if ($resume->{$relation}) {
            $copy->{$relation}()->create($this->snapshotRow($resume->{$relation}->toArray()));
        }
    }

    private function copyRows(Resume $resume, Resume $copy, string $relation): void
    {
        foreach ($resume->{$relation} as $row) {
            $copy->{$relation}()->create($this->snapshotRow($row->toArray()));
        }
    }

    private function copyCustomSections(Resume $resume, Resume $copy): void
    {
        foreach ($resume->customSections as $section) {
            $newSection = $copy->customSections()->create(Arr::except($this->snapshotRow($section->toArray()), ['items']));

            foreach ($section->items as $item) {
                $newSection->items()->create(Arr::except($item->toArray(), ['id', 'resume_custom_section_id', 'created_at', 'updated_at', 'deleted_at']));
            }
        }
    }

    private function copyTaxonomy(Resume $resume, Resume $copy, string $relation, array $pivotColumns): void
    {
        $sync = [];

        foreach ($resume->{$relation} as $item) {
            $sync[$item->id] = Arr::only($item->pivot->toArray(), $pivotColumns);
        }

        $copy->{$relation}()->sync($sync);
    }

    private function list(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\n,]+/', $value) ?: [];
        }

        return collect($value)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function lines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim($line, " \t\n\r\0\x0B-*"))
            ->filter()
            ->values()
            ->all();
    }

    private function bool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function uniqueSlug(User $user, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'resume';
        $slug = $base;
        $index = 2;

        while ($user->resumes()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$index++;
        }

        return $slug;
    }
}

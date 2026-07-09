<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\ResumeShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResumeService
{
    public function create(User $user, array $data): Resume
    {
        return DB::transaction(function () use ($user, $data): Resume {
            $resume = $user->resumes()->create([
                'uuid' => (string) Str::uuid(),
                'template_id' => $data['template_id'] ?? null,
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($user, $data['title']),
                'status' => 'draft',
                'target_role' => $data['target_role'] ?? null,
                'target_company' => $data['target_company'] ?? null,
                'language' => $data['language'] ?? 'en',
                'settings' => $this->settingsFromData($data),
            ]);

            $this->syncContent($resume, $data);
            $resume->forceFill([
                'completion_score' => $this->completionScore($resume),
                'search_text' => $this->plainText($resume),
            ])->save();
            $this->createVersion($resume, $user, 'created', 'Initial draft');

            return $resume->refresh()->load(['profile', 'experiences', 'educations', 'template']);
        });
    }

    public function update(Resume $resume, User $user, array $data, string $reason = 'manual'): Resume
    {
        return DB::transaction(function () use ($resume, $user, $data, $reason): Resume {
            $resume->fill([
                'template_id' => $data['template_id'] ?? $resume->template_id,
                'title' => $data['title'] ?? $resume->title,
                'slug' => isset($data['title']) ? $this->uniqueSlug($resume->user, $data['title'], $resume->id) : $resume->slug,
                'target_role' => $data['target_role'] ?? null,
                'target_company' => $data['target_company'] ?? null,
                'language' => $data['language'] ?? $resume->language,
                'settings' => array_replace($resume->settings ?? [], $this->settingsFromData($data)),
            ]);

            if ($reason === 'autosave') {
                $resume->last_autosaved_at = now();
            }

            $resume->save();
            $this->syncContent($resume, $data);
            $resume->forceFill([
                'completion_score' => $this->completionScore($resume),
                'search_text' => $this->plainText($resume),
            ])->save();
            $this->createVersion($resume, $user, $reason, Str::headline($reason));

            return $resume->refresh()->load(['profile', 'experiences', 'educations', 'template']);
        });
    }

    public function duplicate(Resume $resume, User $user): Resume
    {
        return DB::transaction(function () use ($resume, $user): Resume {
            $resume->load(['profile', 'experiences', 'educations']);

            $copy = $resume->replicate(['uuid', 'slug', 'last_autosaved_at', 'last_exported_at', 'archived_at']);
            $copy->uuid = (string) Str::uuid();
            $copy->user_id = $user->id;
            $copy->title = $resume->title.' Copy';
            $copy->slug = $this->uniqueSlug($user, $copy->title);
            $copy->is_archived = false;
            $copy->is_favorite = false;
            $copy->save();

            if ($resume->profile) {
                $copy->profile()->create(Arr::except($resume->profile->toArray(), ['id', 'resume_id', 'created_at', 'updated_at', 'deleted_at']));
            }

            foreach ($resume->experiences as $experience) {
                $copy->experiences()->create(Arr::except($experience->toArray(), ['id', 'resume_id', 'created_at', 'updated_at', 'deleted_at']));
            }

            foreach ($resume->educations as $education) {
                $copy->educations()->create(Arr::except($education->toArray(), ['id', 'resume_id', 'created_at', 'updated_at', 'deleted_at']));
            }

            $this->createVersion($copy, $user, 'duplicated', 'Copied from '.$resume->title);

            return $copy->refresh();
        });
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
        $resume->loadMissing(['profile', 'experiences', 'educations']);
        $settings = $resume->settings ?? [];
        $chunks = [
            $resume->title,
            $resume->target_role,
            $resume->target_company,
            $resume->profile?->full_name,
            $resume->profile?->headline,
            $resume->profile?->email,
            $resume->profile?->phone,
            $resume->profile?->location,
            $settings['summary'] ?? null,
            implode(', ', $settings['skills'] ?? []),
        ];

        foreach ($resume->experiences as $experience) {
            $chunks[] = trim($experience->position.' '.$experience->company.' '.$experience->description);
            $chunks[] = implode(', ', $experience->achievements ?? []);
        }

        foreach ($resume->educations as $education) {
            $chunks[] = trim($education->institution.' '.$education->degree.' '.$education->field_of_study.' '.$education->description);
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($chunks))));
    }

    public function recentFor(User $user, int $limit = 5): Collection
    {
        return $user->resumes()->with(['template', 'profile'])->latest('updated_at')->limit($limit)->get();
    }

    private function syncContent(Resume $resume, array $data): void
    {
        $profile = array_filter($data['profile'] ?? [], fn ($value) => filled($value));
        $resume->profile()->updateOrCreate(['resume_id' => $resume->id], $profile);

        if (array_key_exists('experiences', $data)) {
            $resume->experiences()->delete();
            foreach ($data['experiences'] ?? [] as $index => $experience) {
                if (! filled($experience['company'] ?? null) && ! filled($experience['position'] ?? null)) {
                    continue;
                }

                $resume->experiences()->create([
                    'company' => $experience['company'] ?? 'Company',
                    'position' => $experience['position'] ?? 'Role',
                    'location' => $experience['location'] ?? null,
                    'start_date' => $experience['start_date'] ?? null,
                    'end_date' => $experience['end_date'] ?? null,
                    'is_current' => (bool) ($experience['is_current'] ?? false),
                    'description' => $experience['description'] ?? null,
                    'achievements' => $this->lines($experience['description'] ?? ''),
                    'sort_order' => $index,
                ]);
            }
        }

        if (array_key_exists('educations', $data)) {
            $resume->educations()->delete();
            foreach ($data['educations'] ?? [] as $index => $education) {
                if (! filled($education['institution'] ?? null)) {
                    continue;
                }

                $resume->educations()->create([
                    'institution' => $education['institution'],
                    'degree' => $education['degree'] ?? null,
                    'field_of_study' => $education['field_of_study'] ?? null,
                    'location' => $education['location'] ?? null,
                    'start_date' => $education['start_date'] ?? null,
                    'end_date' => $education['end_date'] ?? null,
                    'is_current' => (bool) ($education['is_current'] ?? false),
                    'description' => $education['description'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    private function createVersion(Resume $resume, User $user, string $reason, string $label): void
    {
        $resume->loadMissing(['profile', 'experiences', 'educations']);
        $snapshot = [
            'resume' => Arr::except($resume->toArray(), ['created_at', 'updated_at', 'deleted_at']),
            'profile' => $resume->profile?->toArray(),
            'experiences' => $resume->experiences->toArray(),
            'educations' => $resume->educations->toArray(),
        ];

        $resume->versions()->create([
            'created_by_user_id' => $user->id,
            'version_number' => ((int) $resume->versions()->max('version_number')) + 1,
            'label' => $label,
            'reason' => $reason,
            'snapshot' => $snapshot,
            'content_hash' => hash('sha256', json_encode($snapshot)),
        ]);
    }

    private function settingsFromData(array $data): array
    {
        return [
            'summary' => $data['summary'] ?? null,
            'skills' => $this->normalizeSkills($data['skills'] ?? []),
        ];
    }

    private function normalizeSkills(mixed $skills): array
    {
        if (is_string($skills)) {
            $skills = preg_split('/[\n,]+/', $skills) ?: [];
        }

        return collect($skills)
            ->map(fn ($skill) => trim((string) $skill))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function lines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim($line, " \t\n\r\0\x0B-•"))
            ->filter()
            ->values()
            ->all();
    }

    private function completionScore(Resume $resume): int
    {
        $resume->loadMissing(['profile', 'experiences', 'educations']);
        $settings = $resume->settings ?? [];
        $score = 15;

        $score += $resume->profile?->full_name ? 10 : 0;
        $score += $resume->profile?->email ? 10 : 0;
        $score += $resume->profile?->headline ? 10 : 0;
        $score += filled($settings['summary'] ?? null) ? 15 : 0;
        $score += $resume->experiences->isNotEmpty() ? 25 : 0;
        $score += $resume->educations->isNotEmpty() ? 10 : 0;
        $score += count($settings['skills'] ?? []) >= 4 ? 15 : 0;

        return min(100, $score);
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

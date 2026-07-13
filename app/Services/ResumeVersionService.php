<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ResumeVersionService
{
    public function create(Resume $resume, User $user, string $reason, string $label): void
    {
        $resume->loadMissing($this->snapshotRelations());

        $snapshot = [
            'resume' => Arr::except($resume->toArray(), ['created_at', 'updated_at', 'deleted_at']),
            'profile' => $resume->profile?->toArray(),
            'summary' => $resume->summary?->toArray(),
            'social_links' => $resume->socialLinks->toArray(),
            'experiences' => $resume->experiences->toArray(),
            'educations' => $resume->educations->toArray(),
            'projects' => $resume->projects->toArray(),
            'skills' => $resume->skills->toArray(),
            'languages' => $resume->languages->toArray(),
            'certifications' => $resume->certifications->toArray(),
            'awards' => $resume->awards->toArray(),
            'references' => $resume->references->toArray(),
            'custom_sections' => $resume->customSections->toArray(),
            'sections' => $resume->sections->toArray(),
        ];
        $hash = hash('sha256', json_encode($snapshot));

        if ($resume->versions()->where('content_hash', $hash)->exists()) {
            return;
        }

        $resume->versions()->create([
            'created_by_user_id' => $user->id,
            'version_number' => ((int) $resume->versions()->max('version_number')) + 1,
            'label' => $label,
            'reason' => $reason,
            'snapshot' => $snapshot,
            'content_hash' => $hash,
        ]);
    }

    public function snapshotRelations(): array
    {
        return [
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
            'template',
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\User;

class ResumeBuilderService
{
    public function __construct(private readonly ResumeService $resumeService) {}

    public function saveDraft(?Resume $resume, User $user, array $data, string $reason = 'manual'): Resume
    {
        if ($resume) {
            return $this->resumeService->update($resume, $user, $data, $reason);
        }

        return $this->resumeService->create($user, $data);
    }

    public function buildPayload(array $data): array
    {
        return [
            'title' => $data['title'] ?? 'Untitled Resume',
            'target_role' => $data['target_role'] ?? null,
            'target_company' => $data['target_company'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'source' => $data['source'] ?? 'builder',
            'summary' => $data['summary'] ?? null,
            'skills' => $data['skills'] ?? [],
            'theme' => $data['theme'] ?? [],
            'profile' => $data['profile'] ?? [],
            'social_links' => $data['social_links'] ?? [],
            'experiences' => $data['experiences'] ?? [],
            'educations' => $data['educations'] ?? [],
            'projects' => $data['projects'] ?? [],
            'languages' => $data['languages'] ?? [],
            'certifications' => $data['certifications'] ?? [],
            'awards' => $data['awards'] ?? [],
            'references' => $data['references'] ?? [],
            'custom_sections' => $data['custom_sections'] ?? [],
            'sections' => $data['sections'] ?? [],
            'import' => $data['import'] ?? null,
        ];
    }
}

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
        $payload = [
            'title' => $data['title'] ?? 'Untitled Resume',
            'target_role' => $data['target_role'] ?? null,
            'target_company' => $data['target_company'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'source' => $data['source'] ?? 'builder',
            'import' => $data['import'] ?? null,
        ];

        $presentCollections = $data['present_collections'] ?? [];

        foreach ([
            'profile', 'theme', 'social_links', 'experiences', 'educations', 'projects', 'skills', 'languages',
            'certifications', 'awards', 'references', 'custom_sections', 'sections',
        ] as $collection) {
            if (array_key_exists($collection, $data) || in_array($collection, $presentCollections, true)) {
                $payload[$collection] = $data[$collection] ?? [];
            }
        }

        if (array_key_exists('summary', $data) || in_array('summary', $presentCollections, true)) {
            $payload['summary'] = $data['summary'] ?? null;
        }

        return $payload;
    }
}

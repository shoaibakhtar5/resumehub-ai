<?php

namespace App\Services;

use App\Models\Resume;

class PreviewService
{
    public function payload(Resume $resume): array
    {
        $resume->loadMissing($this->relations());

        return [
            'resume' => $resume,
            'profile' => $resume->profile,
            'summary' => $resume->summary,
            'socialLinks' => $resume->socialLinks,
            'experiences' => $resume->experiences,
            'educations' => $resume->educations,
            'projects' => $resume->projects,
            'skills' => $resume->skills,
            'languages' => $resume->languages,
            'certifications' => $resume->certifications,
            'awards' => $resume->awards,
            'references' => $resume->references,
            'customSections' => $resume->customSections,
            'sections' => $resume->sections,
            'settings' => $resume->settings ?? [],
            'template' => $resume->template,
        ];
    }

    public function relations(): array
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

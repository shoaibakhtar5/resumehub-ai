<?php

namespace App\Http\Requests;

use App\Models\Resume;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AiActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resumeId = $this->input('resume_id');

        if (! $resumeId) {
            return (bool) $this->user();
        }

        $resume = Resume::query()->find($resumeId);

        return $resume && ($this->user()?->can('update', $resume) ?? false);
    }

    public function rules(): array
    {
        return [
            'resume_id' => ['nullable', Rule::exists(Resume::class, 'id')],
            'feature' => ['required', 'string', 'max:80'],
            'action' => ['required', Rule::in([
                'summary',
                'experience',
                'bullet_points',
                'skills',
                'projects',
                'certifications',
                'achievements',
                'cover_letter',
                'grammar',
                'rewrite_resume',
                'keyword_optimizer',
                'ats',
                'score',
                'interview_questions',
                'review',
                'keywords',
            ])],
            'input' => ['nullable', 'string', 'max:12000'],
            'job_description' => ['nullable', 'string', 'max:12000'],
            'tone' => ['nullable', 'string', 'max:80'],
        ];
    }
}

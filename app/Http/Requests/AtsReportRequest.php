<?php

namespace App\Http\Requests;

use App\Models\Resume;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtsReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resumeId = $this->input('resume_id');

        if (! $resumeId) {
            return (bool) $this->user();
        }

        $resume = Resume::query()->find($resumeId);

        return $resume && ($this->user()?->can('view', $resume) ?? false);
    }

    public function rules(): array
    {
        return [
            'resume_id' => ['nullable', Rule::exists(Resume::class, 'id')],
            'target_job_title' => ['nullable', 'string', 'max:255'],
            'job_description' => ['nullable', 'string', 'max:12000'],
            'resume_text' => ['nullable', 'string', 'max:12000'],
            'resume_file' => ['nullable', 'file', 'mimes:txt,pdf,doc,docx', 'max:5120'],
        ];
    }
}

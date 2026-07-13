<?php

namespace App\Http\Requests\Resume;

use App\Models\Resume;
use Illuminate\Foundation\Http\FormRequest;

class ResumeImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Resume::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'resume_file' => ['required', 'file', 'mimes:pdf,docx,txt', 'max:10240'],
        ];
    }
}

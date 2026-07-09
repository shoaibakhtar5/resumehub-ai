<?php

namespace App\Http\Requests\Resume;

use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResumeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Resume::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'target_company' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
            'template_id' => ['nullable', Rule::exists(Template::class, 'id')],
            'summary' => ['nullable', 'string', 'max:3000'],
            'skills' => ['nullable'],
            'profile.full_name' => ['nullable', 'string', 'max:255'],
            'profile.headline' => ['nullable', 'string', 'max:255'],
            'profile.email' => ['nullable', 'email', 'max:255'],
            'profile.phone' => ['nullable', 'string', 'max:40'],
            'profile.website' => ['nullable', 'url', 'max:255'],
            'profile.location' => ['nullable', 'string', 'max:255'],
            'experiences' => ['nullable', 'array'],
            'experiences.*.company' => ['nullable', 'string', 'max:255'],
            'experiences.*.position' => ['nullable', 'string', 'max:255'],
            'experiences.*.location' => ['nullable', 'string', 'max:255'],
            'experiences.*.start_date' => ['nullable', 'date'],
            'experiences.*.end_date' => ['nullable', 'date'],
            'experiences.*.is_current' => ['nullable', 'boolean'],
            'experiences.*.description' => ['nullable', 'string', 'max:5000'],
            'educations' => ['nullable', 'array'],
            'educations.*.institution' => ['nullable', 'string', 'max:255'],
            'educations.*.degree' => ['nullable', 'string', 'max:255'],
            'educations.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'educations.*.location' => ['nullable', 'string', 'max:255'],
            'educations.*.start_date' => ['nullable', 'date'],
            'educations.*.end_date' => ['nullable', 'date'],
            'educations.*.is_current' => ['nullable', 'boolean'],
            'educations.*.description' => ['nullable', 'string', 'max:3000'],
        ];
    }
}

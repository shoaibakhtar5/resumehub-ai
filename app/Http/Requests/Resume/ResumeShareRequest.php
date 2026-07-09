<?php

namespace App\Http\Requests\Resume;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResumeShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resume = $this->route('resume');

        return $resume && ($this->user()?->can('share', $resume) ?? false);
    }

    public function rules(): array
    {
        return [
            'visibility' => ['required', Rule::in(['unlisted', 'public', 'password'])],
            'password' => ['nullable', 'required_if:visibility,password', 'string', 'min:8', 'max:120'],
            'allow_download' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}

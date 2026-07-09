<?php

namespace App\Http\Requests\Resume;

class ResumeAutosaveRequest extends ResumeUpdateRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'autosave_token' => ['nullable', 'string', 'max:120'],
        ]);
    }
}

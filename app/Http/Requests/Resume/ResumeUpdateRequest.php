<?php

namespace App\Http\Requests\Resume;

class ResumeUpdateRequest extends ResumeStoreRequest
{
    public function authorize(): bool
    {
        $resume = $this->route('resume');

        return $resume && ($this->user()?->can('update', $resume) ?? false);
    }
}

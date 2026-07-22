<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserSettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'timezone' => ['required', 'timezone'],
            'locale' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}(?:[-_][A-Z]{2})?$/'],
        ];
    }
}

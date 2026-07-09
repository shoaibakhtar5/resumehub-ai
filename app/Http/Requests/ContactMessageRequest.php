<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'topic' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:4000'],
        ];
    }
}

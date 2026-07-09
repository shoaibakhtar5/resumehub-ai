<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('admin.access');
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:120'],
            'status' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:5000'],
            'excerpt' => ['nullable', 'string', 'max:5000'],
            'message' => ['nullable', 'string', 'max:5000'],
            'body' => ['nullable', 'string'],
            'key' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable'],
            'slug' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}

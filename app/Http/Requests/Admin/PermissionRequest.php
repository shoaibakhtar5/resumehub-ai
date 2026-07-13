<?php

namespace App\Http\Requests\Admin;

use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('permission');

        return $permission instanceof Permission
            ? (bool) $this->user()?->can('update', $permission)
            : (bool) $this->user()?->can('create', Permission::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique(Permission::class)->ignore($this->route('permission'))],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return ['name.regex' => 'Use lowercase letters, numbers, dots, underscores, or hyphens.'];
    }
}

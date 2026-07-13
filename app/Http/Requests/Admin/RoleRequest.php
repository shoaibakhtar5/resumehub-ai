<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role instanceof Role
            ? (bool) $this->user()?->can('update', $role)
            : (bool) $this->user()?->can('create', Role::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)->ignore($this->route('role'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ];
    }
}

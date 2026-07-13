<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $managedUser = $this->route('user');

        return $managedUser instanceof User && (bool) $this->user()?->can('update', $managedUser);
    }

    public function rules(): array
    {
        /** @var User $managedUser */
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($managedUser)],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ];
    }
}

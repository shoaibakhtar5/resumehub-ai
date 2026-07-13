<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\AdminResourceService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('admin.access');
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:120'],
            'status' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:10000'],
            'excerpt' => ['nullable', 'string', 'max:5000'],
            'message' => ['nullable', 'string', 'max:10000'],
            'body' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'key' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable'],
            'slug' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:100'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'target_company' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'reference' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:80'],
            'discount_type' => ['nullable', 'string', 'max:40'],
            'billing_interval' => ['nullable', 'string', 'max:40'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'discount_value' => ['nullable', 'integer', 'min:0'],
            'max_redemptions' => ['nullable', 'integer', 'min:0'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
            'is_admin' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'current_period_ends_at' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];

        $definition = app(AdminResourceService::class)->definition((string) $this->route('resource'));
        foreach ($definition['fields'] ?? [] as $name => $field) {
            if (($field['required'] ?? false) && isset($rules[$name])) {
                $rules[$name][0] = 'required';
            }
        }

        if ((string) $this->route('resource') === 'users' && ! $this->route('id')) {
            $rules['password'][0] = 'required';
        }

        $resource = (string) $this->route('resource');
        $id = $this->route('id');
        $uniqueFields = [
            'users' => ['email' => 'users'],
            'roles' => ['name' => 'roles'],
            'permissions' => ['name' => 'permissions'],
            'templates' => ['slug' => 'templates'],
            'pages' => ['slug' => 'pages'],
            'plans' => ['slug' => 'plans'],
            'transactions' => ['reference' => 'transactions'],
            'coupons' => ['code' => 'coupons'],
            'email-templates' => ['key' => 'email_templates'],
        ];

        foreach ($uniqueFields[$resource] ?? [] as $field => $table) {
            $rules[$field][] = Rule::unique($table, $field)->ignore($id);
        }

        return $rules;
    }
}

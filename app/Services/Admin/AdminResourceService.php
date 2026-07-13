<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\AiRequest;
use App\Models\Blog;
use App\Models\Coupon;
use App\Models\EmailTemplate;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Resume;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\TeamMember;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminResourceService
{
    public function definition(string $resource): ?array
    {
        $definitions = [
            'users' => $this->resource('Users', User::class, ['name', 'email', 'status', 'is_admin'], [
                'name' => $this->field('Name', 'text', true),
                'email' => $this->field('Email', 'email', true),
                'password' => $this->field('Password', 'password'),
                'status' => $this->select('Status', ['active' => 'Active', 'suspended' => 'Suspended'], true),
                'is_admin' => $this->field('Administrator', 'checkbox'),
                'role_ids' => $this->field('Roles', 'multiselect'),
            ], ['name', 'email', 'password', 'status', 'is_admin'], ['name', 'email', 'status'], ['roles']),
            'roles' => $this->resource('Roles', Role::class, ['name', 'description', 'is_system'], [
                'name' => $this->field('Role name', 'text', true),
                'description' => $this->field('Description', 'textarea'),
                'permission_ids' => $this->field('Permissions', 'multiselect'),
            ], ['name', 'description'], ['name', 'description'], ['permissions']),
            'permissions' => $this->resource('Permissions', Permission::class, ['name', 'description', 'is_system'], [
                'name' => $this->field('Permission name', 'text', true),
                'description' => $this->field('Description', 'textarea'),
            ], ['name', 'description'], ['name', 'description']),
            'templates' => $this->resource('Templates', Template::class, ['name', 'slug', 'status', 'is_premium'], [
                'name' => $this->field('Name', 'text', true),
                'slug' => $this->field('Slug'),
                'description' => $this->field('Description', 'textarea'),
                'status' => $this->select('Status', ['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'], true),
                'is_premium' => $this->field('Premium template', 'checkbox'),
            ], ['name', 'slug', 'description', 'status', 'is_premium'], ['name', 'slug', 'status']),
            'resumes' => $this->resource('Resumes', Resume::class, ['title', 'user.name', 'target_role', 'status'], [
                'user_id' => $this->field('Owner', 'select', true),
                'title' => $this->field('Title', 'text', true),
                'target_role' => $this->field('Target role'),
                'target_company' => $this->field('Target company'),
                'status' => $this->select('Status', ['draft' => 'Draft', 'published' => 'Published'], true),
            ], ['user_id', 'title', 'target_role', 'target_company', 'status'], ['title', 'target_role', 'status'], ['user']),
            'blog' => $this->resource('Blog Posts', Blog::class, ['title', 'slug', 'status', 'published_at'], [
                'title' => $this->field('Title', 'text', true),
                'slug' => $this->field('Slug'),
                'excerpt' => $this->field('Excerpt', 'textarea'),
                'body' => $this->field('Content', 'textarea', true),
                'status' => $this->select('Status', ['draft' => 'Draft', 'published' => 'Published'], true),
            ], ['title', 'slug', 'excerpt', 'status'], ['title', 'slug', 'status']),
            'pages' => $this->resource('Pages', Page::class, ['title', 'slug', 'status', 'updated_at'], [
                'title' => $this->field('Title', 'text', true),
                'slug' => $this->field('Slug'),
                'content' => $this->field('Content', 'textarea'),
                'status' => $this->select('Status', ['draft' => 'Draft', 'published' => 'Published'], true),
                'meta_title' => $this->field('Meta title'),
                'meta_description' => $this->field('Meta description', 'textarea'),
            ], ['title', 'slug', 'content', 'status', 'meta_title', 'meta_description'], ['title', 'slug', 'status']),
            'team' => $this->resource('Team Members', TeamMember::class, ['name', 'role', 'email', 'is_active'], [
                'name' => $this->field('Name', 'text', true),
                'role' => $this->field('Role', 'text', true),
                'email' => $this->field('Email', 'email'),
                'bio' => $this->field('Biography', 'textarea'),
                'is_active' => $this->field('Active', 'checkbox'),
            ], ['name', 'role', 'email', 'bio', 'is_active'], ['name', 'role', 'email']),
            'plans' => $this->resource('Plans & Pricing', Plan::class, ['name', 'price_cents', 'billing_interval', 'is_active'], [
                'name' => $this->field('Name', 'text', true),
                'slug' => $this->field('Slug'),
                'description' => $this->field('Description', 'textarea'),
                'price' => $this->field('Price (USD)', 'number', true),
                'billing_interval' => $this->select('Billing interval', ['month' => 'Monthly', 'year' => 'Yearly'], true),
                'trial_days' => $this->field('Trial days', 'number'),
                'is_active' => $this->field('Active', 'checkbox'),
            ], ['name', 'slug', 'description', 'price_cents', 'billing_interval', 'trial_days', 'is_active'], ['name', 'slug', 'billing_interval']),
            'subscriptions' => $this->resource('Subscriptions', Subscription::class, ['user.name', 'plan.name', 'status', 'current_period_ends_at'], [
                'user_id' => $this->field('User', 'select', true),
                'plan_id' => $this->field('Plan', 'select', true),
                'status' => $this->select('Status', ['active' => 'Active', 'trialing' => 'Trialing', 'canceled' => 'Canceled', 'past_due' => 'Past due'], true),
                'quantity' => $this->field('Quantity', 'number'),
                'current_period_ends_at' => $this->field('Renews / ends at', 'datetime-local'),
            ], ['user_id', 'plan_id', 'status', 'quantity', 'current_period_ends_at'], ['status'], ['user', 'plan']),
            'transactions' => $this->resource('Transactions', Transaction::class, ['reference', 'user.name', 'amount_cents', 'status'], [
                'user_id' => $this->field('User', 'select', true),
                'reference' => $this->field('Reference'),
                'amount' => $this->field('Amount (USD)', 'number', true),
                'provider' => $this->field('Provider'),
                'status' => $this->select('Status', ['pending' => 'Pending', 'completed' => 'Completed', 'failed' => 'Failed', 'refunded' => 'Refunded'], true),
                'paid_at' => $this->field('Paid at', 'datetime-local'),
            ], ['user_id', 'reference', 'amount_cents', 'provider', 'status', 'paid_at'], ['reference', 'provider', 'status'], ['user']),
            'coupons' => $this->resource('Coupons', Coupon::class, ['code', 'name', 'discount_value', 'is_active'], [
                'code' => $this->field('Code', 'text', true),
                'name' => $this->field('Name', 'text', true),
                'discount_type' => $this->select('Discount type', ['percent' => 'Percent', 'fixed' => 'Fixed amount'], true),
                'discount_value' => $this->field('Discount value', 'number', true),
                'max_redemptions' => $this->field('Maximum redemptions', 'number'),
                'expires_at' => $this->field('Expires at', 'datetime-local'),
                'is_active' => $this->field('Active', 'checkbox'),
            ], ['code', 'name', 'discount_type', 'discount_value', 'max_redemptions', 'expires_at', 'is_active'], ['code', 'name']),
            'settings' => $this->resource('Settings', Setting::class, ['group', 'key', 'value', 'is_public'], [
                'group' => $this->field('Group', 'text', true),
                'key' => $this->field('Key', 'text', true),
                'value' => $this->field('Value', 'textarea'),
                'is_public' => $this->field('Public setting', 'checkbox'),
            ], ['group', 'key', 'value', 'is_public'], ['group', 'key']),
            'email-templates' => $this->resource('Email Templates', EmailTemplate::class, ['name', 'key', 'subject', 'is_active'], [
                'name' => $this->field('Name', 'text', true),
                'key' => $this->field('Key', 'text', true),
                'subject' => $this->field('Subject', 'text', true),
                'body' => $this->field('Email body', 'textarea', true),
                'is_active' => $this->field('Active', 'checkbox'),
            ], ['name', 'key', 'subject', 'body', 'is_active'], ['name', 'key', 'subject']),
            'notifications' => $this->resource('Notifications', AdminNotification::class, ['data', 'read_at', 'created_at'], [
                'user_id' => $this->field('Recipient', 'select', true),
                'title' => $this->field('Title', 'text', true),
                'message' => $this->field('Message', 'textarea', true),
                'status' => $this->select('Status', ['unread' => 'Unread', 'read' => 'Read'], true),
            ], [], ['type'], ['user']),
            'logs' => $this->resource('Activity Logs', ActivityLog::class, ['event', 'description', 'causer_id', 'created_at'], [
                'event' => $this->field('Event', 'text', true),
                'description' => $this->field('Description', 'textarea'),
            ], ['event', 'description'], ['event', 'description']),
            'ai-usage' => $this->resource('AI Usage', AiRequest::class, ['feature', 'action', 'status', 'cost_estimate', 'created_at'], [], [], ['feature', 'action', 'status'], [], true),
        ];

        return $definitions[$resource] ?? null;
    }

    public function options(string $resource): array
    {
        return match ($resource) {
            'users' => ['role_ids' => Role::query()->orderBy('name')->pluck('name', 'id')],
            'roles' => ['permission_ids' => Permission::query()->orderBy('name')->pluck('name', 'id')],
            'resumes', 'transactions', 'notifications' => ['user_id' => User::query()->orderBy('name')->pluck('name', 'id')],
            'subscriptions' => [
                'user_id' => User::query()->orderBy('name')->pluck('name', 'id'),
                'plan_id' => Plan::query()->orderBy('sort_order')->pluck('name', 'id'),
            ],
            default => [],
        };
    }

    public function payload(Request $request, string $resource, array $definition, ?Model $record = null): array
    {
        $payload = Arr::only($request->all(), $definition['fillable']);

        foreach ($definition['fields'] as $key => $field) {
            if (($field['type'] ?? null) === 'checkbox' && in_array($key, $definition['fillable'], true)) {
                $payload[$key] = $request->boolean($key);
            }
        }

        if (in_array('slug', $definition['fillable'], true) && blank($payload['slug'] ?? null)) {
            $payload['slug'] = Str::slug($payload['title'] ?? $payload['name'] ?? Str::random(8));
        }

        if ($resource === 'users') {
            if (blank($payload['password'] ?? null)) {
                unset($payload['password']);
            } else {
                $payload['password'] = Hash::make($payload['password']);
            }
        }

        if ($resource === 'resumes' && ! $record) {
            $payload['uuid'] = (string) Str::uuid();
        }

        if ($resource === 'templates') {
            $payload += ['version' => '1.0.0', 'entry_html' => 'resume.html', 'entry_css' => 'style.css'];
        }

        if ($resource === 'blog') {
            $payload['content'] = $request->input('body', $record?->content ?? '');
            $payload['author_user_id'] = $request->user()->id;
            $payload['published_at'] = ($payload['status'] ?? null) === 'published' ? ($record?->published_at ?? now()) : null;
        }

        if ($resource === 'pages') {
            $payload['author_user_id'] = $request->user()->id;
            $payload['published_at'] = ($payload['status'] ?? null) === 'published' ? ($record?->published_at ?? now()) : null;
        }

        if ($resource === 'plans') {
            $payload['price_cents'] = (int) round(((float) $request->input('price', 0)) * 100);
        }

        if ($resource === 'transactions') {
            $payload['amount_cents'] = (int) round(((float) $request->input('amount', 0)) * 100);
            $payload['reference'] = ($payload['reference'] ?? null) ?: 'TXN-'.Str::upper(Str::random(10));
            $payload['currency'] = 'USD';
        }

        if ($resource === 'settings') {
            $payload['value'] = ['text' => $request->input('value')];
            $payload['type'] = 'string';
            $payload['updated_by_user_id'] = $request->user()->id;
        }

        if ($resource === 'notifications') {
            return [
                'id' => $record?->id ?? (string) Str::uuid(),
                'type' => 'App\\Notifications\\AdminMessage',
                'notifiable_type' => User::class,
                'notifiable_id' => $request->integer('user_id'),
                'data' => ['title' => $request->input('title'), 'message' => $request->input('message')],
                'read_at' => $request->input('status') === 'read' ? now() : null,
            ];
        }

        return $payload;
    }

    public function syncRelations(Model $record, Request $request, string $resource): void
    {
        if ($resource === 'users') {
            $record->roles()->sync($request->input('role_ids', []));
        }

        if ($resource === 'roles') {
            $record->permissions()->sync($request->input('permission_ids', []));
        }
    }

    private function resource(string $title, string $model, array $columns, array $fields, array $fillable, array $searchable, array $with = [], bool $readonly = false): array
    {
        return compact('title', 'model', 'columns', 'fields', 'fillable', 'searchable', 'with', 'readonly');
    }

    private function field(string $label, string $type = 'text', bool $required = false): array
    {
        return compact('label', 'type', 'required');
    }

    private function select(string $label, array $options, bool $required = false): array
    {
        return ['label' => $label, 'type' => 'select', 'options' => $options, 'required' => $required];
    }
}

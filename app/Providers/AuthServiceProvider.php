<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Resume;
use App\Models\Role;
use App\Models\Template;
use App\Models\User;
use App\Policies\PermissionPolicy;
use App\Policies\ResumePolicy;
use App\Policies\RolePolicy;
use App\Policies\TemplatePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Resume::class => ResumePolicy::class,
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Template::class => TemplatePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

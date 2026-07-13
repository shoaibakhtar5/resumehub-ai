<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'admin.access' => 'Access the administration panel',
            'users.manage' => 'Manage user accounts',
            'roles.manage' => 'Manage administrator roles',
            'permissions.manage' => 'Manage role permissions',
        ])->map(fn (string $description, string $name) => Permission::query()->updateOrCreate(
            ['name' => $name],
            ['guard_name' => 'web', 'description' => $description, 'is_system' => true],
        ));
        $role = Role::query()->updateOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web', 'description' => 'Full platform administration', 'is_system' => true],
        );
        $role->permissions()->syncWithoutDetaching($permissions->pluck('id'));

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@resumehub.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Admin@12345'),
                'is_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'locale' => 'en',
            ],
        );
        $admin->roles()->syncWithoutDetaching([$role->id]);
    }
}

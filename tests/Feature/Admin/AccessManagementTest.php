<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccessManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_module_supports_crud_profile_image_status_roles_search_and_filters(): void
    {
        Storage::fake('public');
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@resumehub.test')->firstOrFail();
        $editor = Role::query()->create(['name' => 'editor', 'guard_name' => 'web']);
        $reviewer = Role::query()->create(['name' => 'reviewer', 'guard_name' => 'web']);

        $this->actingAs($admin)->get(route('admin.users'))
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee('Add User');

        $this->post(route('admin.users.store'), [
            'name' => 'Taylor Editor',
            'email' => 'taylor@example.com',
            'phone' => '+1 555 0100',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'status' => 'active',
            'profile_photo' => UploadedFile::fake()->createWithContent(
                'avatar.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
            'role_ids' => [$editor->id],
        ])->assertSessionHasNoErrors();

        $managedUser = User::query()->where('email', 'taylor@example.com')->firstOrFail();
        $this->assertStringContainsString('/storage/profile-photos/', $managedUser->profile_photo_path);
        Storage::disk('public')->assertExists(Str::after($managedUser->profile_photo_path, '/storage/'));
        $this->assertDatabaseHas('role_user', [
            'user_id' => $managedUser->id,
            'role_id' => $editor->id,
            'assigned_by_user_id' => $admin->id,
        ]);

        $this->get(route('admin.users', ['search' => 'Taylor', 'status' => 'active', 'role_id' => $editor->id]))
            ->assertOk()
            ->assertSee('Taylor Editor');

        $this->patch(route('admin.users.update', $managedUser), [
            'name' => 'Taylor Reviewer',
            'email' => 'taylor@example.com',
            'phone' => '+1 555 0100',
            'status' => 'inactive',
            'role_ids' => [$reviewer->id],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $managedUser));

        $managedUser->refresh();
        $this->assertSame('inactive', $managedUser->status);
        $this->assertSame([$reviewer->id], $managedUser->roles()->pluck('roles.id')->all());
        $this->assertDatabaseMissing('role_user', ['user_id' => $managedUser->id, 'role_id' => $editor->id]);

        $this->get(route('admin.users.show', $managedUser))
            ->assertOk()
            ->assertSee('Taylor Reviewer')
            ->assertSee('Inactive');

        $this->delete(route('admin.users.destroy', $managedUser))->assertRedirect(route('admin.users'));
        $this->assertSoftDeleted('users', ['id' => $managedUser->id]);
    }

    public function test_roles_crud_syncs_permissions_exactly(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@resumehub.test')->firstOrFail();
        $view = Permission::query()->create(['name' => 'reports.view', 'guard_name' => 'web']);
        $export = Permission::query()->create(['name' => 'reports.export', 'guard_name' => 'web']);
        $this->actingAs($admin);

        $this->post(route('admin.roles.store'), [
            'name' => 'analyst',
            'description' => 'Reporting analyst',
            'permission_ids' => [$view->id],
        ])->assertSessionHasNoErrors();

        $role = Role::query()->where('name', 'analyst')->firstOrFail();
        $this->assertSame([$view->id], $role->permissions()->pluck('permissions.id')->all());

        $this->patch(route('admin.roles.update', $role), [
            'name' => 'senior-analyst',
            'description' => 'Senior reporting analyst',
            'permission_ids' => [$export->id],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.show', $role));

        $role->refresh();
        $this->assertSame('senior-analyst', $role->name);
        $this->assertSame([$export->id], $role->permissions()->pluck('permissions.id')->all());
        $this->assertDatabaseMissing('permission_role', ['role_id' => $role->id, 'permission_id' => $view->id]);

        $this->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles'));
        $this->assertSoftDeleted('roles', ['id' => $role->id]);
    }

    public function test_permissions_crud_and_validation_work(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@resumehub.test')->firstOrFail();
        $this->actingAs($admin);

        $this->post(route('admin.permissions.store'), [
            'name' => 'billing.view',
            'description' => 'View billing data',
        ])->assertSessionHasNoErrors();

        $permission = Permission::query()->where('name', 'billing.view')->firstOrFail();
        $this->patch(route('admin.permissions.update', $permission), [
            'name' => 'billing.manage',
            'description' => 'Manage billing data',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.permissions.show', $permission));
        $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'name' => 'billing.manage']);

        $this->post(route('admin.permissions.store'), ['name' => 'Invalid Permission'])
            ->assertSessionHasErrors('name');

        $this->delete(route('admin.permissions.destroy', $permission))->assertRedirect(route('admin.permissions'));
        $this->assertSoftDeleted('permissions', ['id' => $permission->id]);
    }

    public function test_policies_block_underprivileged_admins_and_protect_system_records(): void
    {
        $adminAccess = Permission::query()->create([
            'name' => 'admin.access',
            'guard_name' => 'web',
            'is_system' => true,
        ]);
        $adminRole = Role::query()->create([
            'name' => 'admin',
            'guard_name' => 'web',
            'is_system' => true,
        ]);
        $adminRole->permissions()->attach($adminAccess);
        $limitedAdmin = User::factory()->create(['is_admin' => false, 'email_verified_at' => now()]);
        $limitedAdmin->roles()->attach($adminRole);

        $this->actingAs($limitedAdmin)->get(route('admin.users'))->assertForbidden();
        $this->get(route('admin.roles'))->assertForbidden();
        $this->get(route('admin.permissions'))->assertForbidden();

        $superAdmin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
        $this->actingAs($superAdmin);
        $this->delete(route('admin.users.destroy', $superAdmin))->assertForbidden();
        $this->delete(route('admin.roles.destroy', $adminRole))->assertForbidden();
        $this->delete(route('admin.permissions.destroy', $adminAccess))->assertForbidden();

        $this->get(route('admin.resources.create', ['resource' => 'users']))->assertNotFound();
    }
}

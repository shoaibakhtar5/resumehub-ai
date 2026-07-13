<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_credentials_are_seeded_and_can_log_in(): void
    {
        $this->seed(AdminUserSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@resumehub.test',
            'password' => 'Admin@12345',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs(User::query()->where('email', 'admin@resumehub.test')->first());
    }

    public function test_admin_is_redirected_away_from_the_user_dashboard(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now(), 'is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_routes_reject_non_admin_users(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'is_admin' => false]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_dashboard_and_every_sidebar_destination_load(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now(), 'is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Resume Creation Trend', escape: false)
            ->assertSee('System Overview');

        foreach ([
            'users', 'roles', 'permissions', 'templates', 'resumes', 'blog', 'pages', 'team',
            'ai-usage', 'plans', 'subscriptions', 'transactions', 'coupons', 'settings',
            'email-templates', 'notifications', 'logs', 'system-status',
        ] as $route) {
            $this->get(route('admin.'.$route))->assertOk();
        }
    }

    public function test_admin_can_create_update_and_delete_a_resource(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now(), 'is_admin' => true]);
        $this->actingAs($admin);

        $this->post(route('admin.resources.store', ['resource' => 'pages']), [
            'title' => 'About ResumeHub',
            'slug' => 'about-resumehub',
            'content' => 'Platform information.',
            'status' => 'draft',
        ])->assertRedirect(route('admin.pages'));

        $page = Page::query()->where('slug', 'about-resumehub')->firstOrFail();
        $this->patch(route('admin.resources.update', ['resource' => 'pages', 'id' => $page->id]), [
            'title' => 'About ResumeHub AI',
            'slug' => 'about-resumehub',
            'content' => 'Updated platform information.',
            'status' => 'published',
        ])->assertRedirect(route('admin.pages'));

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'About ResumeHub AI']);
        $this->delete(route('admin.resources.destroy', ['resource' => 'pages', 'id' => $page->id]))
            ->assertRedirect(route('admin.pages'));
        $this->assertSoftDeleted('pages', ['id' => $page->id]);

        $bulkPage = Page::query()->create([
            'author_user_id' => $admin->id,
            'title' => 'Bulk Page',
            'slug' => 'bulk-page',
            'status' => 'draft',
        ]);
        $this->post(route('admin.resources.bulk', ['resource' => 'pages']), [
            'ids' => [(string) $bulkPage->id],
            'action' => 'delete',
        ])->assertRedirect();
        $this->assertSoftDeleted('pages', ['id' => $bulkPage->id]);
    }

    public function test_all_admin_managed_resources_accept_valid_create_requests(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now(), 'is_admin' => true]);
        $this->actingAs($admin);

        $requests = [
            'templates' => ['name' => 'Admin Template', 'status' => 'published'],
            'resumes' => ['user_id' => $admin->id, 'title' => 'Admin Resume', 'status' => 'draft'],
            'blog' => ['title' => 'Admin Blog', 'body' => 'Article content', 'status' => 'draft'],
            'pages' => ['title' => 'Admin Page', 'content' => 'Page content', 'status' => 'draft'],
            'team' => ['name' => 'Operations User', 'role' => 'Operations'],
            'plans' => ['name' => 'Professional', 'price' => '19.00', 'billing_interval' => 'month'],
            'transactions' => ['user_id' => $admin->id, 'amount' => '19.00', 'status' => 'completed'],
            'coupons' => ['code' => 'WELCOME20', 'name' => 'Welcome', 'discount_type' => 'percent', 'discount_value' => 20],
            'settings' => ['group' => 'site', 'key' => 'brand_name', 'value' => 'ResumeHub AI'],
            'email-templates' => ['name' => 'Welcome email', 'key' => 'welcome', 'subject' => 'Welcome', 'body' => 'Welcome to ResumeHub AI.'],
            'notifications' => ['user_id' => $admin->id, 'title' => 'System update', 'message' => 'The update is complete.', 'status' => 'unread'],
            'logs' => ['event' => 'admin.test', 'description' => 'Admin CRUD verification'],
        ];

        foreach ($requests as $resource => $payload) {
            $this->post(route('admin.resources.store', ['resource' => $resource]), $payload)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.'.$resource));
        }

        $plan = Plan::query()->where('slug', 'professional')->firstOrFail();
        $this->post(route('admin.resources.store', ['resource' => 'subscriptions']), [
            'user_id' => $admin->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'quantity' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.subscriptions'));
    }
}

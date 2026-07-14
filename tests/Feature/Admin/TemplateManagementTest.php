<?php

namespace Tests\Feature\Admin;

use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\User;
use App\Services\TemplateRenderingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    }

    public function test_admin_can_upload_html_template_and_it_appears_in_builder(): void
    {
        $category = TemplateCategory::query()->create(['name' => 'Professional', 'slug' => 'professional', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'template_category_id' => $category->id,
            'template_file' => UploadedFile::fake()->createWithContent('professional.html', $this->html()),
        ]));

        $template = Template::query()->where('slug', 'precision-pro')->firstOrFail();
        $response->assertRedirect(route('admin.templates.preview', $template));
        Storage::disk('local')->assertExists($template->package_path);
        $this->actingAs($this->admin)->get(route('admin.templates'))->assertOk()->assertSee('Precision Pro');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->get(route('resume.builder'))->assertOk()->assertSee('Precision Pro');
    }

    public function test_template_upload_rejects_unsafe_html_unknown_and_missing_placeholders(): void
    {
        $unsafe = '<html><body><h1>{{ full_name }}</h1><script>alert(1)</script>{{ experiences }}</body></html>';
        $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'template_file' => UploadedFile::fake()->createWithContent('unsafe.html', $unsafe),
        ]))->assertSessionHasErrors('template_file');

        $unknown = '<html><body><h1>{{ full_name }}</h1>{{ secret_value }}{{ experiences }}</body></html>';
        $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'slug' => 'unknown-template', 'name' => 'Unknown Template',
            'template_file' => UploadedFile::fake()->createWithContent('unknown.txt', $unknown),
        ]))->assertSessionHasErrors('template_file');

        $missing = '<html><body><h1>Resume</h1>{{ experiences }}</body></html>';
        $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'slug' => 'missing-template', 'name' => 'Missing Template',
            'template_file' => UploadedFile::fake()->createWithContent('missing.html', $missing),
        ]))->assertSessionHasErrors('template_file');
    }

    public function test_admin_can_update_duplicate_toggle_feature_preview_and_delete_unused_template(): void
    {
        $template = $this->createTemplate();

        $this->actingAs($this->admin)->get(route('admin.templates.preview', $template))
            ->assertOk()->assertSee('Alex Morgan')->assertSee('Northstar Labs');

        $this->actingAs($this->admin)->patch(route('admin.templates.featured', $template))->assertRedirect();
        $this->assertTrue($template->fresh()->is_featured);

        $this->actingAs($this->admin)->patch(route('admin.templates.status', $template))->assertRedirect();
        $this->assertSame('disabled', $template->fresh()->status);

        $this->actingAs($this->admin)->post(route('admin.templates.duplicate', $template))->assertRedirect();
        $copy = Template::query()->where('name', 'Precision Pro Copy')->firstOrFail();
        $this->assertSame('draft', $copy->status);
        Storage::disk('local')->assertExists($copy->package_path);

        $this->actingAs($this->admin)->delete(route('admin.templates.destroy', $copy))->assertRedirect(route('admin.templates'));
        $this->assertSoftDeleted($copy);
    }

    public function test_renderer_replaces_values_escapes_user_content_and_formats_dates(): void
    {
        $template = $this->createTemplate();
        $html = app(TemplateRenderingService::class)->render($template, [
            'profile' => ['full_name' => '<b>Pat Lee</b>', 'headline' => 'Engineer'],
            'experiences' => [[
                'position' => 'Lead Engineer', 'company' => 'Acme', 'start_date' => '2024-01-01',
                'is_current' => true, 'description' => '<script>bad()</script> Built products.',
            ]],
        ]);

        $this->assertStringContainsString('&lt;b&gt;Pat Lee&lt;/b&gt;', $html);
        $this->assertStringContainsString('Jan 2024 – Present', $html);
        $this->assertStringNotContainsString('<script>bad()', $html);
        $this->assertStringNotContainsString('{{ full_name }}', $html);
    }

    private function createTemplate(): Template
    {
        $template = Template::query()->create([
            'created_by_user_id' => $this->admin->id, 'name' => 'Precision Pro', 'slug' => 'precision-pro',
            'status' => 'published', 'version' => '1.0.1', 'sort_order' => 1,
            'config' => ['primary_color' => '#4f46e5', 'supported_sections' => ['experiences']],
        ]);
        $path = 'templates/'.$template->id.'/1.0.1/resume.html';
        Storage::disk('local')->put($path, $this->html());
        $template->update(['package_path' => $path]);

        return $template;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Precision Pro', 'slug' => 'precision-pro', 'description' => 'A precise professional template.',
            'template_file' => UploadedFile::fake()->createWithContent('precision.html', $this->html()),
            'primary_color' => '#4f46e5', 'font_family' => 'Inter, Arial, sans-serif',
            'supported_sections' => ['experiences', 'education', 'skills'], 'preview_images' => [],
            'status' => 'published', 'is_featured' => '1', 'is_premium' => '0', 'sort_order' => 1,
        ], $overrides);
    }

    private function html(): string
    {
        return '<!doctype html><html><head><style>body{font-family:Arial}</style></head><body><h1>{{ full_name }}</h1><p>{{ job_title }}</p>{{ summary }}{{ experiences }}{{ education }}{{ skills }}</body></html>';
    }
}

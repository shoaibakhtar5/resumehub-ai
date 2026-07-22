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

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $template = Template::query()->where('slug', 'precision-pro')->firstOrFail();
        $response->assertRedirect(route('admin.templates.preview', $template));
        Storage::disk('local')->assertExists($template->package_path);
        $this->assertSame('html', $template->source_type);
        $this->assertSame('professional.html', data_get($template->config, 'source_original_name'));
        $this->assertNotEmpty(data_get($template->config, 'source_checksum'));
        $this->actingAs($this->admin)->get(route('admin.templates'))->assertOk()->assertSee('Precision Pro');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->get(route('resume.builder'))->assertOk()->assertSee('Precision Pro');
    }

    public function test_admin_can_upload_txt_markup_and_aliases_are_normalized(): void
    {
        $markup = '<html><body><h1>[[ name ]]</h1><p>${ title }</p>%% professional_summary %%[[ work_experience ]]</body></html>';

        $response = $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'name' => 'TXT Template',
            'slug' => 'txt-template',
            'template_file' => UploadedFile::fake()->createWithContent('resume.txt', $markup),
        ]));

        $template = Template::query()->where('slug', 'txt-template')->firstOrFail();
        $response->assertRedirect(route('admin.templates.preview', $template));
        $this->assertSame('txt', $template->source_type);
        $stored = Storage::disk('local')->get($template->package_path);
        $this->assertStringContainsString('{{ full_name }}', $stored);
        $this->assertStringContainsString('{{ job_title }}', $stored);
        $this->assertStringContainsString('{{ summary }}', $stored);
        $this->assertStringContainsString('{{ experiences }}', $stored);
    }

    public function test_placeholder_free_html_is_detected_from_dom_and_preserves_layout(): void
    {
        $html = <<<'HTML'
<!doctype html><html><head><style>.resume-grid{display:grid}.hero{color:#123456}</style></head><body>
<main class="resume-grid"><header class="hero"><img class="avatar" src="portrait.jpg"><h1>Jane Candidate</h1><p>Product Designer</p><a href="mailto:jane@example.com">jane@example.com</a><span class="phone">+1 555 010 0200</span><span class="location">Austin, Texas</span></header>
<section class="summary-block"><h2>Professional Summary</h2><p>Old summary content.</p></section>
<section class="career-block"><h2>Work Experience</h2><article><h3>Designer</h3><p>Old company content.</p></article></section>
<section><h2>Skills</h2><p>Figma, Research</p></section></main></body></html>
HTML;

        $response = $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'name' => 'DOM Detected', 'slug' => 'dom-detected',
            'template_file' => UploadedFile::fake()->createWithContent('detected.html', $html),
        ]));

        $template = Template::query()->where('slug', 'dom-detected')->firstOrFail();
        $response->assertRedirect(route('admin.templates.preview', $template));
        $this->assertSame('published', $template->status);
        $this->assertFalse((bool) data_get($template->config, 'requires_mapping'));
        $stored = Storage::disk('local')->get($template->package_path);
        $this->assertStringContainsString('class="resume-grid"', $stored);
        $this->assertStringContainsString('.hero{color:#123456}', $stored);
        $this->assertStringContainsString('{{ full_name }}', $stored);
        $this->assertStringContainsString('{{ job_title }}', $stored);
        $this->assertStringContainsString('{{ photo }}', $stored);
        $this->assertStringContainsString('{{ summary }}', $stored);
        $this->assertStringContainsString('{{ experiences }}', $stored);
        $this->assertStringNotContainsString('Old company content.', $stored);

        $rendered = app(TemplateRenderingService::class)->render($template, [
            'profile' => ['full_name' => 'Alex Morgan', 'headline' => 'Senior Designer', 'photo_path' => '/storage/profile-photos/alex.jpg'],
            'summary' => 'Current summary', 'experiences' => [['position' => 'Lead Designer', 'company' => 'Northstar']],
            'skills' => [['name' => 'Research']],
        ]);
        $this->assertStringContainsString('class="avatar" src="/storage/profile-photos/alex.jpg"', $rendered);
        $this->assertStringNotContainsString('src="&lt;img', $rendered);
    }

    public function test_uncertain_fields_are_saved_as_draft_and_can_be_mapped(): void
    {
        $html = '<html><body><h1>Jane Candidate</h1><p>Engineer</p><div class="contact-detail">Islamabad, Pakistan</div><section><h2>Summary</h2><p>Old summary.</p></section></body></html>';
        $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'name' => 'Needs Mapping', 'slug' => 'needs-mapping',
            'template_file' => UploadedFile::fake()->createWithContent('mapping.html', $html),
        ]))->assertRedirect();

        $template = Template::query()->where('slug', 'needs-mapping')->firstOrFail();
        $this->assertSame('draft', $template->status);
        $this->assertTrue((bool) data_get($template->config, 'requires_mapping'));
        $candidate = collect(data_get($template->config, 'mapping_candidates'))->first();
        $this->assertNotEmpty($candidate['id'] ?? null);
        $this->actingAs($this->admin)->get(route('admin.templates.edit', $template))
            ->assertOk()->assertSee('Complete automatic field mapping')->assertSee('Islamabad, Pakistan');
        $this->actingAs($this->admin)->patch(route('admin.templates.status', $template))
            ->assertSessionHasErrors('status');
        $this->assertSame('draft', $template->fresh()->status);

        $this->actingAs($this->admin)->patch(route('admin.templates.update', $template), $this->payload([
            'name' => 'Needs Mapping', 'slug' => 'needs-mapping', 'status' => 'published',
            'template_file' => null,
            'template_mappings' => [$candidate['id'] => 'location'],
        ]))->assertRedirect(route('admin.templates'));

        $template->refresh();
        $this->assertSame('published', $template->status);
        $this->assertFalse((bool) data_get($template->config, 'requires_mapping'));
        $this->assertStringContainsString('{{ location }}', Storage::disk('local')->get($template->package_path));
    }

    public function test_admin_can_upload_latex_and_processed_template_renders_resume_data(): void
    {
        $latex = <<<'TEX'
\documentclass{article}
\begin{document}
\begin{center}
\textbf{\placeholder{full_name}}\\
\placeholder{job_title}
\end{center}
\section*{Professional Summary}
\placeholder{summary}
\section*{Experience}
\placeholder{experiences}
\section*{Skills}
\placeholder{skills}
\end{document}
TEX;

        $response = $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'name' => 'LaTeX Executive',
            'slug' => 'latex-executive',
            'template_file' => UploadedFile::fake()->createWithContent('executive.tex', $latex),
        ]));

        $template = Template::query()->where('slug', 'latex-executive')->firstOrFail();
        $response->assertRedirect(route('admin.templates.preview', $template));
        $this->assertSame('latex', $template->source_type);
        $this->assertSame('executive.tex', data_get($template->config, 'source_original_name'));
        $this->assertContains('experiences', data_get($template->config, 'detected_placeholders'));

        $stored = Storage::disk('local')->get($template->package_path);
        $this->assertStringContainsString('<!doctype html>', strtolower($stored));
        $this->assertStringNotContainsString('\\documentclass', $stored);

        $rendered = app(TemplateRenderingService::class)->render($template, app(TemplateRenderingService::class)->demoPayload());
        $this->assertStringContainsString('Alex Morgan', $rendered);
        $this->assertStringContainsString('Northstar Labs', $rendered);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->get(route('resume.builder'))->assertOk()->assertSee('LaTeX Executive');
    }

    public function test_source_type_filter_and_metadata_only_update_work(): void
    {
        $template = $this->createTemplate();
        $template->update(['config' => array_merge($template->config, [
            'source_type' => 'latex',
            'source_original_name' => 'source.tex',
            'source_checksum' => 'stable-checksum',
            'detected_placeholders' => ['full_name', 'experiences'],
        ])]);

        $this->actingAs($this->admin)->get(route('admin.templates', ['source_type' => 'latex']))
            ->assertOk()
            ->assertSee('Precision Pro');
        $this->actingAs($this->admin)->get(route('admin.templates', ['source_type' => 'html']))
            ->assertOk()
            ->assertDontSee('Precision Pro');

        $this->actingAs($this->admin)->patch(route('admin.templates.update', $template), $this->payload([
            'name' => 'Precision Updated',
            'slug' => 'precision-updated',
            'template_file' => null,
        ]))->assertRedirect(route('admin.templates'));

        $template->refresh();
        $this->assertSame('Precision Updated', $template->name);
        $this->assertSame('latex', data_get($template->config, 'source_type'));
        $this->assertSame('stable-checksum', data_get($template->config, 'source_checksum'));
    }

    public function test_template_upload_rejects_unsafe_html_unknown_placeholders_and_unsupported_files(): void
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

        $this->actingAs($this->admin)->post(route('admin.templates.store'), $this->payload([
            'slug' => 'unsupported-template', 'name' => 'Unsupported Template',
            'template_file' => UploadedFile::fake()->createWithContent('unsupported.pdf', '%PDF invalid'),
        ]))->assertSessionHasErrors('template_file');
    }

    public function test_admin_can_update_duplicate_toggle_feature_preview_and_delete_unused_template(): void
    {
        $template = $this->createTemplate();

        $this->actingAs($this->admin)->get(route('admin.templates.preview', $template))
            ->assertOk()->assertSee('Alex Morgan')->assertSee('Northstar Labs')->assertSee('Print test');

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

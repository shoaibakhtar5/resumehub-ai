<?php

namespace Tests\Feature;

use App\Livewire\LiveResumePreview;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ResumeBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_builder_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/resume-builder')
            ->assertOk()
            ->assertSee('Personal Information')
            ->assertSee('Education')
            ->assertSee('Experience')
            ->assertSee('Skills')
            ->assertSee('Projects')
            ->assertSee('Languages')
            ->assertSee('Professional Summary')
            ->assertSee('Review')
            ->assertSee('resume-live-preview')
            ->assertSee('novalidate', false);
    }

    public function test_resume_builder_lists_only_published_templates_created_by_admin(): void
    {
        $user = User::factory()->create();
        $template = Template::query()->create([
            'created_by_user_id' => $user->id,
            'name' => 'Admin Corporate Template',
            'slug' => 'admin-corporate-template',
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->get('/resume-builder')
            ->assertOk()
            ->assertSee('Admin Corporate Template');

        $this->post('/resumes', [
            'title' => 'Corporate Resume',
            'template_id' => $template->id,
            'profile' => ['full_name' => 'Template User'],
        ])->assertRedirect();

        $this->assertDatabaseHas('resumes', [
            'user_id' => $user->id,
            'template_id' => $template->id,
        ]);
    }

    public function test_resume_builder_can_persist_profile_summary_experience_and_sections(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/resumes', [
            'title' => 'Senior Engineer Resume',
            'target_role' => 'Senior Engineer',
            'summary' => 'Experienced Laravel engineer with a product mindset.',
            'profile' => [
                'full_name' => 'Jane Doe',
                'headline' => 'Senior Laravel Engineer',
                'email' => 'jane@example.com',
                'phone' => '+1 555 1234',
                'city' => 'Berlin',
                'country' => 'Germany',
                'postal_code' => '10115',
                'website' => 'https://jane.dev',
                'metadata' => [
                    'address' => 'Main Street 1',
                    'date_of_birth' => '1990-01-01',
                    'nationality' => 'German',
                    'linkedin' => 'https://linkedin.com/in/jane',
                    'github' => 'https://github.com/jane',
                    'twitter' => 'https://x.com/jane',
                ],
            ],
            'experiences' => [[
                'company' => 'Acme Labs',
                'position' => 'Senior Engineer',
                'location' => 'Remote',
                'description' => 'Built a resilient platform.',
                'is_current' => true,
            ]],
            'educations' => [[
                'institution' => 'MIT',
                'degree' => 'BSc',
                'field_of_study' => 'Computer Science',
                'description' => 'Graduated with honors.',
            ]],
            'skills' => 'Laravel, Livewire, PHP',
            'languages' => [[
                'name' => 'English',
                'proficiency' => 'Professional',
            ]],
            'projects' => [[
                'name' => 'ResumeHub Importer',
                'role' => 'Lead Engineer',
                'description' => 'Built structured import workflows.',
            ]],
            'certifications' => [[
                'name' => 'AWS Certified Developer',
                'issuer' => 'Amazon',
            ]],
            'awards' => [[
                'title' => 'Engineering Excellence',
                'issuer' => 'Acme Labs',
            ]],
            'references' => [[
                'name' => 'Alex Manager',
                'email' => 'alex@example.com',
                'is_visible' => true,
            ]],
            'custom_sections' => [[
                'title' => 'Publications',
                'description' => 'Laravel architecture notes.',
                'items' => [[
                    'title' => 'Scaling Laravel',
                    'description' => 'A practical field guide.',
                ]],
            ]],
            'sections' => [
                ['section_key' => 'experience', 'is_visible' => true, 'sort_order' => 1],
                ['section_key' => 'education', 'is_visible' => true, 'sort_order' => 2],
                ['section_key' => 'projects', 'is_visible' => false, 'sort_order' => 3],
            ],
        ]);

        $response->assertRedirect();

        $resume = $user->resumes()->firstOrFail();

        $this->assertSame('Jane Doe', $resume->profile->full_name);
        $this->assertSame('Experienced Laravel engineer with a product mindset.', $resume->settings['summary']);
        $this->assertSame('Acme Labs', $resume->experiences()->first()->company);
        $this->assertSame('MIT', $resume->educations()->first()->institution);
        $this->assertSame(['Laravel', 'Livewire', 'PHP'], $resume->settings['skills']);
        $this->assertSame('ResumeHub Importer', $resume->projects()->first()->name);
        $this->assertSame('English', $resume->languages()->first()->name);
        $this->assertSame('AWS Certified Developer', $resume->certifications()->first()->name);
        $this->assertSame('Engineering Excellence', $resume->awards()->first()->title);
        $this->assertSame('Alex Manager', $resume->references()->first()->name);
        $this->assertSame('Publications', $resume->customSections()->first()->title);
        $this->assertDatabaseHas('resume_summaries', ['resume_id' => $resume->id, 'word_count' => 7]);
        $this->assertDatabaseHas('resume_sections', ['resume_id' => $resume->id, 'section_key' => 'projects', 'is_visible' => false]);

        $this->get(route('resumes.edit', $resume))
            ->assertOk()
            ->assertSee('Save as Draft');
    }

    public function test_resume_builder_normalizes_domain_only_urls_before_saving(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/resumes', [
            'title' => 'URL Resume',
            'profile' => [
                'full_name' => 'URL User',
                'website' => 'example.com/portfolio',
            ],
            'social_links' => [[
                'platform' => 'linkedin',
                'url' => 'linkedin.com/in/url-user',
            ]],
            'projects' => [[
                'name' => 'Example Project',
                'url' => 'example.com/project',
                'repository_url' => 'github.com/example/project',
            ]],
        ])->assertRedirect();

        $resume = $user->resumes()->firstOrFail();

        $this->assertSame('https://example.com/portfolio', $resume->profile->website);
        $this->assertSame('https://linkedin.com/in/url-user', $resume->socialLinks()->first()->url);
        $this->assertSame('https://example.com/project', $resume->projects()->first()->url);
        $this->assertSame('https://github.com/example/project', $resume->projects()->first()->repository_url);
    }

    public function test_resume_builder_autosaves_and_persists_a_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/resumes', [
            'title' => 'Product Engineer Resume',
            'target_role' => 'Product Engineer',
            'profile' => [
                'full_name' => 'Jordan Lee',
                'website' => 'jordan.dev',
            ],
        ])->assertRedirect();

        $resume = $user->resumes()->firstOrFail();
        $photo = UploadedFile::fake()->createWithContent(
            'profile.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        );

        $response = $this->post(route('resumes.autosave', $resume), [
            'title' => 'Product Engineer Resume',
            'target_role' => 'Lead Product Engineer',
            'summary' => 'Builds reliable products with measurable customer impact.',
            'profile' => [
                'full_name' => 'Jordan Lee',
                'website' => 'portfolio.jordan.dev',
            ],
            'profile_photo' => $photo,
        ]);

        $response->assertOk()
            ->assertJson(['saved' => true]);

        $this->assertStringStartsWith('/storage/resume-photos/', $response->json('photo_url'));

        $resume->refresh()->load('profile');

        $this->assertSame('Lead Product Engineer', $resume->target_role);
        $this->assertSame('https://portfolio.jordan.dev', $resume->profile->website);
        $this->assertNotNull($resume->last_autosaved_at);
        $this->assertStringStartsWith('/storage/resume-photos/', $resume->profile->photo_path);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $resume->profile->photo_path));
    }

    public function test_autosave_preserves_unsupported_sections_and_stable_row_ids_without_creating_versions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/resumes', [
            'title' => 'Stable Resume',
            'profile' => ['full_name' => 'Taylor Reed', 'website' => 'https://example.com'],
            'experiences' => [[
                'company' => 'Example Company',
                'position' => 'Engineer',
            ]],
        ])->assertRedirect();

        $resume = $user->resumes()->firstOrFail();
        $experience = $resume->experiences()->firstOrFail();
        $resume->certifications()->create([
            'name' => 'Cloud Certification',
            'issuer' => 'Example Institute',
            'is_visible' => true,
        ]);
        $versionCount = $resume->versions()->count();

        $this->post(route('resumes.autosave', $resume), [
            'title' => 'Stable Resume',
            'profile' => ['full_name' => 'Taylor Reed', 'website' => ''],
            'present_collections' => ['experiences', 'educations', 'projects', 'social_links', 'skills', 'languages', 'sections'],
            'experiences' => [[
                'id' => $experience->id,
                'company' => 'Example Company',
                'position' => 'Senior Engineer',
                'is_visible' => true,
                'sort_order' => 0,
            ]],
            'theme' => [
                'accent_color' => '#153e75',
                'font_pairing' => 'modern',
                'density' => 'balanced',
                'page_size' => 'a4',
            ],
        ])->assertOk()->assertJson(['saved' => true]);

        $resume->refresh()->load(['profile', 'experiences', 'certifications']);
        $this->assertNull($resume->profile->website);
        $this->assertSame($experience->id, $resume->experiences->first()->id);
        $this->assertSame('Senior Engineer', $resume->experiences->first()->position);
        $this->assertSame('Cloud Certification', $resume->certifications->first()->name);
        $this->assertSame($versionCount, $resume->versions()->count());
        $this->assertSame('#153e75', data_get($resume->settings, 'theme.accent_color'));
    }

    public function test_live_preview_removes_deleted_collection_items(): void
    {
        Livewire::test(LiveResumePreview::class)
            ->dispatch('resume-updated', data: [
                'experiences' => [
                    ['company' => 'Keep Company', 'position' => 'Engineer'],
                    ['company' => 'Remove Company', 'position' => 'Intern'],
                ],
            ])
            ->assertSee('Remove Company')
            ->dispatch('resume-updated', data: [
                'experiences' => [
                    ['company' => 'Keep Company', 'position' => 'Engineer'],
                ],
            ])
            ->assertSee('Keep Company')
            ->assertDontSee('Remove Company');
    }

    public function test_live_preview_updates_from_builder_events_without_a_page_refresh(): void
    {
        Livewire::test(LiveResumePreview::class)
            ->dispatch('resume-updated', data: [
                'title' => 'Instant Preview Resume',
                'target_role' => 'Staff Engineer',
                'summary' => 'This content was sent directly from the active wizard step.',
                'profile' => [
                    'full_name' => 'Avery Chen',
                    'email' => 'avery@example.com',
                ],
                'theme' => ['accent_color' => '#0f7a5a'],
                'skills' => [['name' => 'Laravel']],
                'educations' => [['institution' => 'Example University', 'degree' => 'BSc']],
                'experiences' => [['company' => 'Example Company', 'position' => 'Engineer']],
                'projects' => [['name' => 'Example Project']],
                'languages' => [['name' => 'English', 'proficiency' => 'Fluent']],
                'certifications' => [['name' => 'AWS Certified Developer', 'issuer' => 'Amazon']],
            ])
            ->assertSee('Avery Chen')
            ->assertSee('Staff Engineer')
            ->assertSee('This content was sent directly from the active wizard step.')
            ->assertSee('Laravel')
            ->assertSee('Example University')
            ->assertSee('Example Company')
            ->assertSee('Example Project')
            ->assertSee('English')
            ->assertSee('AWS Certified Developer');
    }

    public function test_live_preview_formats_dates_hides_empty_sections_and_handles_long_content(): void
    {
        $longName = 'Alexandria Montgomery-Wellington International Engineering Leader';

        Livewire::test(LiveResumePreview::class)
            ->dispatch('resume-updated', data: [
                'profile' => [
                    'full_name' => $longName,
                    'headline' => 'Principal Platform and Distributed Systems Engineering Architect',
                ],
                'experiences' => [[
                    'company' => 'Example Company',
                    'position' => 'Principal Engineer',
                    'start_date' => '2024-01-01T00:00:00.000000Z',
                    'is_current' => true,
                    'description' => str_repeat('Designed resilient systems with measurable business impact. ', 8),
                ]],
                'educations' => [],
                'projects' => [['name' => '', 'description' => '']],
            ])
            ->assertSee($longName)
            ->assertSee('Jan 2024 – Present')
            ->assertDontSee('2024-01-01T00:00:00.000000Z')
            ->assertDontSee('Education')
            ->assertDontSee('Projects')
            ->assertSee('resume-preview-name', false);
    }

    public function test_live_preview_changes_template_immediately_and_preserves_persisted_sections(): void
    {
        $user = User::factory()->create();
        $template = Template::query()->create([
            'created_by_user_id' => $user->id,
            'name' => 'Executive Flow',
            'slug' => 'executive-flow',
            'status' => 'published',
        ]);

        $this->actingAs($user)->post('/resumes', [
            'title' => 'Executive Resume',
            'profile' => ['full_name' => 'Morgan Reed'],
            'certifications' => [[
                'name' => 'Professional Architecture Certification',
                'issuer' => 'Architecture Board',
                'issued_at' => '2023-06-01',
            ]],
        ])->assertRedirect();

        $resume = $user->resumes()->firstOrFail();

        Livewire::test(LiveResumePreview::class, ['resume' => $resume])
            ->assertSee('Professional Architecture Certification')
            ->assertSee('Jun 2023')
            ->dispatch('resume-updated', data: [
                'profile' => ['full_name' => 'Morgan Reed Updated'],
                'template_id' => $template->id,
            ])
            ->assertSet('templateVariant', 'executive')
            ->assertSee('resume-template-executive', false)
            ->assertSee('Morgan Reed Updated')
            ->assertSee('Professional Architecture Certification');
    }

    public function test_resume_builder_can_download_pdf_share_update_and_delete(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/resumes', [
            'title' => 'Platform Engineer Resume',
            'profile' => ['full_name' => 'Taylor Morgan'],
            'summary' => 'Platform engineer focused on resilient systems.',
        ])->assertRedirect();

        $resume = $user->resumes()->firstOrFail();

        $this->patch(route('resumes.update', $resume), [
            'title' => 'Senior Platform Engineer Resume',
            'profile' => ['full_name' => 'Taylor Morgan'],
            'summary' => 'Senior platform engineer focused on resilient systems.',
        ])->assertRedirect(route('resumes.edit', $resume));

        $this->assertDatabaseHas('resumes', [
            'id' => $resume->id,
            'title' => 'Senior Platform Engineer Resume',
        ]);

        $this->post(route('resumes.download', $resume), ['format' => 'pdf'])
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->post(route('resumes.share', $resume), [
            'visibility' => 'unlisted',
            'allow_download' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('resume_shares', [
            'resume_id' => $resume->id,
            'visibility' => 'unlisted',
            'allow_download' => true,
        ]);

        $this->delete(route('resumes.destroy', $resume))
            ->assertRedirect(route('resumes.index'));

        $this->assertSoftDeleted('resumes', ['id' => $resume->id]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\User;
use App\Services\ResumeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'admin.access',
            'users.manage',
            'resumes.manage',
            'templates.create',
            'templates.update',
            'templates.delete',
            'blog.manage',
            'settings.manage',
            'analytics.view',
            'logs.view',
        ])->mapWithKeys(fn (string $name) => [
            $name => Permission::query()->updateOrCreate(
                ['name' => $name],
                ['guard_name' => 'web', 'description' => Str::headline($name), 'is_system' => true]
            ),
        ]);

        $adminRole = Role::query()->updateOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web', 'description' => 'Full platform administration', 'is_system' => true]
        );
        $adminRole->permissions()->sync($permissions->pluck('id'));

        $memberRole = Role::query()->updateOrCreate(
            ['name' => 'member'],
            ['guard_name' => 'web', 'description' => 'Resume builder user', 'is_system' => true]
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@resumehub.ai'],
            [
                'name' => 'ResumeHub Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'locale' => 'en',
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $user = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'locale' => 'en',
            ]
        );
        $user->roles()->syncWithoutDetaching([$memberRole->id]);

        $categories = collect([
            ['name' => 'Creative', 'description' => 'Polished visual layouts for design and marketing roles.'],
            ['name' => 'Corporate', 'description' => 'Classic layouts for operations, finance, legal, and leadership.'],
            ['name' => 'Technology', 'description' => 'Structured layouts for engineering, data, and product roles.'],
        ])->mapWithKeys(fn (array $data) => [
            $data['name'] => TemplateCategory::query()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                $data + ['sort_order' => 0, 'is_active' => true]
            ),
        ]);

        foreach ([
            ['Neo-Minimalist', 'Creative', 'Best for designers and artists', true],
            ['Executive Flow', 'Corporate', 'Best for leadership and finance', false],
            ['Syntax Master', 'Technology', 'Best for software engineers', false],
            ['Standard Global', 'Corporate', 'Best for legal and administrative roles', false],
        ] as $index => [$name, $category, $description, $premium]) {
            Template::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'template_category_id' => $categories[$category]->id,
                    'created_by_user_id' => $admin->id,
                    'name' => $name,
                    'description' => $description,
                    'status' => 'published',
                    'version' => '1.0.0',
                    'preview_path' => '/assets/stitch/templates-gallery.png',
                    'entry_html' => 'resume.html',
                    'entry_css' => 'style.css',
                    'config' => ['accent' => $index % 2 === 0 ? 'primary' : 'neutral'],
                    'is_premium' => $premium,
                    'sort_order' => $index,
                ]
            );
        }

        $blogCategory = BlogCategory::query()->updateOrCreate(
            ['slug' => 'guides'],
            ['name' => 'Guides', 'description' => 'Resume, ATS, and AI workflow guidance.', 'is_active' => true]
        );

        foreach (['ATS', 'AI writing', 'Templates'] as $tag) {
            BlogTag::query()->updateOrCreate(
                ['slug' => Str::slug($tag)],
                ['name' => $tag, 'description' => 'ResumeHub AI editorial tag']
            );
        }

        foreach (config('resumehub.blog_posts') as $post) {
            Blog::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'blog_category_id' => $blogCategory->id,
                    'author_user_id' => $admin->id,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'content' => "Modern resume work is faster when structure, evidence, and targeting live in one place.\n\nResumeHub AI combines templates, ATS checks, and practical AI suggestions so each draft can improve before export.",
                    'status' => 'published',
                    'published_at' => now()->subDays(random_int(1, 12)),
                ]
            );
        }

        foreach ([
            ['site', 'support_email', 'support@resumehub.ai', true],
            ['site', 'default_cta', 'Build Your Resume', true],
            ['ai', 'default_tone', 'Confident and concise', false],
            ['ai', 'monthly_free_generations', '25', false],
        ] as [$group, $key, $value, $public]) {
            Setting::query()->updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => ['text' => $value], 'type' => 'string', 'is_public' => $public, 'updated_by_user_id' => $admin->id]
            );
        }

        foreach ([
            ['Sara Malik', 'Operations Lead', 'Owns support quality and platform operations.', 'sara@resumehub.ai'],
            ['Ava Chen', 'Template Designer', 'Maintains resume layout quality and visual systems.', 'ava@resumehub.ai'],
        ] as [$name, $role, $bio, $email]) {
            TeamMember::query()->updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'role' => $role, 'bio' => $bio, 'is_active' => true]
            );
        }

        if (! $user->resumes()->exists()) {
            app(ResumeService::class)->create($user, [
                'title' => 'Product Designer Resume',
                'target_role' => 'Senior Product Designer',
                'target_company' => 'ResumeHub AI',
                'template_id' => Template::query()->where('slug', 'neo-minimalist')->value('id'),
                'summary' => 'Product designer focused on AI-assisted SaaS workflows, design systems, and measurable product outcomes.',
                'skills' => 'Design systems, UX research, Figma, Product strategy, Accessibility, Experimentation',
                'profile' => [
                    'full_name' => $user->name,
                    'headline' => 'Senior Product Designer',
                    'email' => $user->email,
                    'phone' => '+1 555 010 2400',
                    'location' => 'Remote',
                ],
                'experiences' => [[
                    'company' => 'DesignLab',
                    'position' => 'Senior Product Designer',
                    'description' => "Led a dashboard redesign that improved task completion by 32%.\nBuilt a reusable design system adopted by four product teams.",
                    'is_current' => true,
                ]],
                'educations' => [[
                    'institution' => 'State University',
                    'degree' => 'BFA',
                    'field_of_study' => 'Interaction Design',
                ]],
            ]);
        }
    }
}

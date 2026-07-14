@php
    $resume?->loadMissing([
        'profile', 'summary', 'socialLinks', 'experiences', 'educations', 'projects', 'skills',
        'languages', 'sections', 'template',
    ]);

    $settings = $resume?->settings ?? [];
    $theme = old('theme', $settings['theme'] ?? []);
    $date = fn ($value) => $value ? $value->format('Y-m-d') : '';
    $ensureRows = fn (array $rows, array $blank) => $rows === [] ? [$blank] : array_values($rows);
    $selectedTemplate = old('template_id', $selectedTemplate ?? $resume?->template_id);
    $templateOptions = ['' => 'No template'];

    foreach (($templates ?? collect()) as $template) {
        $templateOptions[$template->id] = $template->name;
    }

    $wizardSteps = [
        ['label' => 'Personal', 'title' => 'Personal Information'],
        ['label' => 'Education', 'title' => 'Education'],
        ['label' => 'Experience', 'title' => 'Experience'],
        ['label' => 'Skills', 'title' => 'Skills'],
        ['label' => 'Projects', 'title' => 'Projects'],
        ['label' => 'Languages', 'title' => 'Languages'],
        ['label' => 'Summary', 'title' => 'Professional Summary'],
        ['label' => 'Review', 'title' => 'Review Your Resume'],
    ];

    $profile = old('profile', [
        'full_name' => $resume?->profile?->full_name ?? auth()->user()->name,
        'headline' => $resume?->profile?->headline ?? $resume?->target_role,
        'email' => $resume?->profile?->email ?? auth()->user()->email,
        'phone' => $resume?->profile?->phone ?? auth()->user()->phone,
        'website' => $resume?->profile?->website,
        'location' => $resume?->profile?->location,
        'photo_path' => $resume?->profile?->photo_path,
    ]);

    $socialLinks = $ensureRows(old('social_links', $resume?->socialLinks?->map(fn ($link) => [
        'platform' => $link->platform,
        'label' => $link->label,
        'url' => $link->url,
        'is_visible' => $link->is_visible,
        'sort_order' => $link->sort_order,
    ])->values()->all() ?? []), ['platform' => 'linkedin', 'label' => 'LinkedIn', 'url' => '', 'is_visible' => true, 'sort_order' => 0]);

    $educations = $ensureRows(old('educations', $resume?->educations?->map(fn ($item) => [
        'institution' => $item->institution,
        'degree' => $item->degree,
        'field_of_study' => $item->field_of_study,
        'location' => $item->location,
        'start_date' => $date($item->start_date),
        'end_date' => $date($item->end_date),
        'is_current' => $item->is_current,
        'grade' => $item->grade,
        'description' => $item->description,
        'is_visible' => $item->is_visible,
        'sort_order' => $item->sort_order,
    ])->values()->all() ?? []), ['institution' => '', 'degree' => '', 'field_of_study' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'grade' => '', 'description' => '', 'is_visible' => true, 'sort_order' => 0]);

    $experiences = $ensureRows(old('experiences', $resume?->experiences?->map(fn ($item) => [
        'company' => $item->company,
        'position' => $item->position,
        'employment_type' => $item->employment_type,
        'location' => $item->location,
        'start_date' => $date($item->start_date),
        'end_date' => $date($item->end_date),
        'is_current' => $item->is_current,
        'description' => $item->description,
        'technologies' => implode(', ', $item->technologies ?? []),
        'is_visible' => $item->is_visible,
        'sort_order' => $item->sort_order,
    ])->values()->all() ?? []), ['company' => '', 'position' => '', 'employment_type' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '', 'technologies' => '', 'is_visible' => true, 'sort_order' => 0]);

    $skills = $ensureRows(old('skills', $resume?->skills?->map(fn ($item) => [
        'name' => $item->name,
        'category' => $item->pivot?->category,
        'proficiency' => $item->pivot?->proficiency,
        'years_experience' => $item->pivot?->years_experience,
        'is_visible' => (bool) ($item->pivot?->is_visible ?? true),
        'sort_order' => (int) ($item->pivot?->sort_order ?? 0),
    ])->values()->all() ?? []), ['name' => '', 'category' => '', 'proficiency' => '', 'years_experience' => '', 'is_visible' => true, 'sort_order' => 0]);

    $projects = $ensureRows(old('projects', $resume?->projects?->map(fn ($item) => [
        'name' => $item->name,
        'role' => $item->role,
        'url' => $item->url,
        'repository_url' => $item->repository_url,
        'start_date' => $date($item->start_date),
        'end_date' => $date($item->end_date),
        'is_current' => $item->is_current,
        'description' => $item->description,
        'technologies' => implode(', ', $item->technologies ?? []),
        'is_visible' => $item->is_visible,
        'sort_order' => $item->sort_order,
    ])->values()->all() ?? []), ['name' => '', 'role' => '', 'url' => '', 'repository_url' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '', 'technologies' => '', 'is_visible' => true, 'sort_order' => 0]);

    $languages = $ensureRows(old('languages', $resume?->languages?->map(fn ($item) => [
        'name' => $item->name,
        'iso_code' => $item->iso_code,
        'proficiency' => $item->pivot?->proficiency,
        'is_visible' => (bool) ($item->pivot?->is_visible ?? true),
        'sort_order' => (int) ($item->pivot?->sort_order ?? 0),
    ])->values()->all() ?? []), ['name' => '', 'iso_code' => '', 'proficiency' => '', 'is_visible' => true, 'sort_order' => 0]);

    $sections = [
        ['section_key' => 'summary', 'title' => 'Professional Summary', 'is_visible' => true, 'sort_order' => 0],
        ['section_key' => 'education', 'title' => 'Education', 'is_visible' => true, 'sort_order' => 1],
        ['section_key' => 'experience', 'title' => 'Experience', 'is_visible' => true, 'sort_order' => 2],
        ['section_key' => 'skills', 'title' => 'Skills', 'is_visible' => true, 'sort_order' => 3],
        ['section_key' => 'projects', 'title' => 'Projects', 'is_visible' => true, 'sort_order' => 4],
        ['section_key' => 'languages', 'title' => 'Languages', 'is_visible' => true, 'sort_order' => 5],
    ];

    $builderData = [
        'formId' => 'resume-builder-form',
        'csrfToken' => csrf_token(),
        'autosaveUrl' => $resume ? route('resumes.autosave', $resume) : null,
        'activeStep' => 0,
        'previewOpen' => false,
        'completionScore' => $resume?->completion_score ?? 0,
        'title' => old('title', $resume?->title ?? auth()->user()->name.' Resume'),
        'target_role' => old('target_role', $resume?->target_role ?? ''),
        'target_company' => old('target_company', $resume?->target_company ?? ''),
        'template_id' => $selectedTemplate,
        'summary' => old('summary', $resume?->summary?->content ?? $settings['summary'] ?? ''),
        'profile' => $profile,
        'social_links' => $socialLinks,
        'educations' => $educations,
        'experiences' => $experiences,
        'skills' => $skills,
        'projects' => $projects,
        'languages' => $languages,
        'sections' => $sections,
        'theme' => [
            'accent_color' => $theme['accent_color'] ?? '#5b2be0',
            'font_pairing' => $theme['font_pairing'] ?? 'modern',
            'density' => $theme['density'] ?? 'balanced',
            'page_size' => $theme['page_size'] ?? 'a4',
        ],
        'settings' => $settings,
        'photoPreview' => $profile['photo_path'] ?? null,
    ];
@endphp

<x-app-layout title="Resume Builder" mode="user">
    <div x-data="resumeBuilder(@js($builderData))" x-init="init()" class="resume-builder-shell -mt-4 space-y-4 pb-20 lg:pb-16">
        <header class="flex flex-col gap-3 border-b border-border-light pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('resumes.index') }}" class="mb-2 inline-flex items-center gap-2 text-label-sm text-on-surface-variant transition hover:text-primary">
                    <span aria-hidden="true">←</span> Back to Resumes
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-display text-headline-lg text-on-surface" x-text="title || 'Resume'">{{ $builderData['title'] }}</h1>
                    <span class="inline-flex items-center gap-1 rounded-full bg-success/10 px-2.5 py-1 text-label-sm text-success">
                        <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                        <span x-text="autosaveState === 'Ready' ? '{{ $resume ? 'Saved' : 'Draft' }}' : autosaveState"></span>
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button type="button" variant="white" size="sm" icon="eye" x-on:click="openPreview()">Preview</x-ui.button>
                @if ($resume)
                    <form method="POST" action="{{ route('resumes.download', $resume) }}">
                        @csrf
                        <input type="hidden" name="format" value="pdf">
                        <x-ui.button type="submit" variant="white" size="sm" icon="arrow-down-tray">Download PDF</x-ui.button>
                    </form>
                    <form method="POST" action="{{ route('resumes.share', $resume) }}">
                        @csrf
                        <input type="hidden" name="visibility" value="unlisted">
                        <input type="hidden" name="allow_download" value="1">
                        <x-ui.button type="submit" size="sm" icon="share">Share</x-ui.button>
                    </form>
                @endif
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-body-sm text-on-surface">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-body-sm text-danger">
                Please review the highlighted fields before saving.
            </div>
        @endif

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.12fr)_minmax(360px,0.88fr)]">
            <section class="resume-builder-card overflow-hidden rounded-xl border border-border-light bg-white shadow-soft">
                <nav class="rh-scrollbar-hide flex overflow-x-auto border-b border-border-light px-2 py-2" aria-label="Resume steps">
                    @foreach ($wizardSteps as $index => $step)
                        <button type="button" class="flex min-w-max items-center gap-1.5 rounded-md px-2 py-1.5 text-label-sm transition" x-bind:class="activeStep === {{ $index }} ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container'" x-on:click="goToStep({{ $index }})">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px]" x-bind:class="activeStep === {{ $index }} ? 'bg-primary text-white' : 'bg-surface-container text-on-surface-variant'">{{ $index + 1 }}</span>
                            <span>{{ $step['label'] }}</span>
                        </button>
                    @endforeach
                </nav>

                <form id="resume-builder-form" class="resume-builder-form" method="POST" enctype="multipart/form-data" action="{{ $resume ? route('resumes.update', $resume) : route('resumes.store') }}" novalidate x-on:input.debounce.400ms="queueAutosave()">
                    @csrf
                    @if ($resume) @method('PATCH') @endif

                    <input type="hidden" name="title" x-model="title">
                    <input type="hidden" name="target_company" x-model="target_company">
                    @foreach (['social_links', 'experiences', 'educations', 'projects', 'skills', 'languages', 'sections'] as $collection)
                        <input type="hidden" name="present_collections[]" value="{{ $collection }}">
                    @endforeach
                    <input type="hidden" name="theme[accent_color]" x-model="theme.accent_color">
                    <input type="hidden" name="theme[font_pairing]" x-model="theme.font_pairing">
                    <input type="hidden" name="theme[density]" x-model="theme.density">
                    <input type="hidden" name="theme[page_size]" x-model="theme.page_size">
                    @foreach ($sections as $index => $section)
                        <input type="hidden" name="sections[{{ $index }}][section_key]" value="{{ $section['section_key'] }}">
                        <input type="hidden" name="sections[{{ $index }}][title]" value="{{ $section['title'] }}">
                        <input type="hidden" name="sections[{{ $index }}][is_visible]" value="1">
                        <input type="hidden" name="sections[{{ $index }}][sort_order]" value="{{ $section['sort_order'] }}">
                    @endforeach

                    <div class="resume-builder-editor p-4 sm:p-5">
                        <template x-for="(step, index) in {{ Js::from($wizardSteps) }}" :key="index">
                            <div x-show="activeStep === index" x-transition.opacity>
                                <h2 class="font-display text-headline-md text-on-surface" x-text="step.title"></h2>
                            </div>
                        </template>

                        <section x-show="activeStep === 0" x-transition.opacity class="mt-4 space-y-4">
                            <div class="grid gap-4 md:grid-cols-[128px_minmax(0,1fr)]">
                                <div class="text-center">
                                    <button type="button" class="relative mx-auto block h-24 w-24 overflow-hidden rounded-full border-4 border-surface-container bg-surface-container text-primary shadow-soft" x-on:click="$refs.photoInput.click()">
                                        <template x-if="photoPreview"><img :src="photoPreview" alt="Profile preview" class="h-full w-full object-cover"></template>
                                        <template x-if="!photoPreview"><span class="flex h-full w-full items-center justify-center text-4xl font-bold" x-text="(profile.full_name || 'R').charAt(0).toUpperCase()"></span></template>
                                        <span class="absolute bottom-0.5 right-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full border border-border-light bg-white text-on-surface shadow-soft"><x-ui.icon name="photo" class="h-4 w-4" /></span>
                                    </button>
                                    <input x-ref="photoInput" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="sr-only" x-on:change="handlePhoto($event)">
                                    <p class="mt-3 text-[11px] text-on-surface-variant">JPG, PNG or WEBP. Max 2MB.</p>
                                    @error('profile_photo')<p class="mt-2 text-body-sm text-danger">{{ $message }}</p>@enderror
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div><label class="rh-label mb-2">Resume Name</label><input class="rh-input" type="text" x-model="title" required></div>
                                    <div><label class="rh-label mb-2">Job Title</label><input class="rh-input" name="target_role" type="text" x-model="target_role"></div>
                                    <div><label class="rh-label mb-2">Full Name</label><input class="rh-input" name="profile[full_name]" type="text" x-model="profile.full_name"></div>
                                    <div><label class="rh-label mb-2">Email</label><input class="rh-input" name="profile[email]" type="email" x-model="profile.email"></div>
                                    <div><label class="rh-label mb-2">Phone</label><input class="rh-input" name="profile[phone]" type="text" x-model="profile.phone"></div>
                                    <div><label class="rh-label mb-2">Location</label><input class="rh-input" name="profile[location]" type="text" x-model="profile.location"></div>
                                    <div><label class="rh-label mb-2">LinkedIn</label><input type="hidden" name="social_links[0][id]" x-model="social_links[0].id"><input class="rh-input" :name="`social_links[0][url]`" type="url" x-model="social_links[0].url"><input type="hidden" name="social_links[0][platform]" value="linkedin"><input type="hidden" name="social_links[0][label]" value="LinkedIn"><input type="hidden" name="social_links[0][is_visible]" value="1"><input type="hidden" name="social_links[0][sort_order]" value="0"></div>
                                    <div><label class="rh-label mb-2">Website</label><input class="rh-input" name="profile[website]" type="url" x-model="profile.website"></div>
                                    <div class="sm:col-span-2"><label class="rh-label mb-2">Professional Headline</label><input class="rh-input" name="profile[headline]" type="text" x-model="profile.headline"></div>
                                </div>
                            </div>
                        </section>

                        <section x-show="activeStep === 1" x-transition.opacity class="mt-6 space-y-4">
                            <template x-for="(item, index) in educations" :key="index">
                                <article class="rounded-lg border border-border-light bg-surface-container-low p-5">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <input type="hidden" :name="`educations[${index}][id]`" x-model="item.id"><input type="hidden" :name="`educations[${index}][sort_order]`" x-model="item.sort_order"><input type="hidden" :name="`educations[${index}][is_visible]`" value="1">
                                        <div><label class="rh-label mb-2">Institution</label><input class="rh-input" :name="`educations[${index}][institution]`" x-model="item.institution"></div>
                                        <div><label class="rh-label mb-2">Degree</label><input class="rh-input" :name="`educations[${index}][degree]`" x-model="item.degree"></div>
                                        <div><label class="rh-label mb-2">Field of Study</label><input class="rh-input" :name="`educations[${index}][field_of_study]`" x-model="item.field_of_study"></div>
                                        <div><label class="rh-label mb-2">Location</label><input class="rh-input" :name="`educations[${index}][location]`" x-model="item.location"></div>
                                        <div><label class="rh-label mb-2">Start Date</label><input class="rh-input" type="date" :name="`educations[${index}][start_date]`" x-model="item.start_date"></div>
                                        <div><label class="rh-label mb-2">End Date</label><input class="rh-input" type="date" :name="`educations[${index}][end_date]`" x-model="item.end_date"></div>
                                        <div class="sm:col-span-2"><label class="rh-label mb-2">Description</label><textarea class="rh-input min-h-24" :name="`educations[${index}][description]`" x-model="item.description"></textarea></div>
                                    </div>
                                    <button type="button" class="mt-4 text-label-sm text-danger" x-on:click="removeItem('educations', index)">Remove education</button>
                                </article>
                            </template>
                            <x-ui.button type="button" variant="secondary" size="sm" icon="plus" x-on:click="addItem('educations', { institution: '', degree: '', field_of_study: '', location: '', start_date: '', end_date: '', description: '' })">Add Education</x-ui.button>
                        </section>

                        <section x-show="activeStep === 2" x-transition.opacity class="mt-6 space-y-4">
                            <template x-for="(item, index) in experiences" :key="index">
                                <article class="rounded-lg border border-border-light bg-surface-container-low p-5">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <input type="hidden" :name="`experiences[${index}][id]`" x-model="item.id"><input type="hidden" :name="`experiences[${index}][sort_order]`" x-model="item.sort_order"><input type="hidden" :name="`experiences[${index}][is_visible]`" value="1">
                                        <div><label class="rh-label mb-2">Company</label><input class="rh-input" :name="`experiences[${index}][company]`" x-model="item.company"></div>
                                        <div><label class="rh-label mb-2">Position</label><input class="rh-input" :name="`experiences[${index}][position]`" x-model="item.position"></div>
                                        <div><label class="rh-label mb-2">Employment Type</label><input class="rh-input" :name="`experiences[${index}][employment_type]`" x-model="item.employment_type"></div>
                                        <div><label class="rh-label mb-2">Location</label><input class="rh-input" :name="`experiences[${index}][location]`" x-model="item.location"></div>
                                        <div><label class="rh-label mb-2">Start Date</label><input class="rh-input" type="date" :name="`experiences[${index}][start_date]`" x-model="item.start_date"></div>
                                        <div><label class="rh-label mb-2">End Date</label><input class="rh-input" type="date" :name="`experiences[${index}][end_date]`" x-model="item.end_date"></div>
                                        <div class="sm:col-span-2"><label class="rh-label mb-2">Responsibilities and Achievements</label><textarea class="rh-input min-h-32" :name="`experiences[${index}][description]`" x-model="item.description"></textarea></div>
                                        <div class="sm:col-span-2"><label class="rh-label mb-2">Technologies</label><input class="rh-input" :name="`experiences[${index}][technologies]`" x-model="item.technologies"></div>
                                    </div>
                                    <button type="button" class="mt-4 text-label-sm text-danger" x-on:click="removeItem('experiences', index)">Remove experience</button>
                                </article>
                            </template>
                            <x-ui.button type="button" variant="secondary" size="sm" icon="plus" x-on:click="addItem('experiences', { company: '', position: '', employment_type: '', location: '', start_date: '', end_date: '', description: '', technologies: '' })">Add Experience</x-ui.button>
                        </section>

                        <section x-show="activeStep === 3" x-transition.opacity class="mt-6 space-y-4">
                            <template x-for="(item, index) in skills" :key="index">
                                <article class="grid items-end gap-4 rounded-lg border border-border-light bg-surface-container-low p-4 sm:grid-cols-[1.4fr_1fr_1fr_auto]">
                                    <input type="hidden" :name="`skills[${index}][sort_order]`" x-model="item.sort_order"><input type="hidden" :name="`skills[${index}][is_visible]`" value="1">
                                    <div><label class="rh-label mb-2">Skill</label><input class="rh-input" :name="`skills[${index}][name]`" x-model="item.name"></div>
                                    <div><label class="rh-label mb-2">Category</label><input class="rh-input" :name="`skills[${index}][category]`" x-model="item.category"></div>
                                    <div><label class="rh-label mb-2">Proficiency</label><select class="rh-input" :name="`skills[${index}][proficiency]`" x-model="item.proficiency"><option value="">Select</option><option>Beginner</option><option>Intermediate</option><option>Advanced</option><option>Expert</option></select></div>
                                    <button type="button" class="min-h-11 px-2 text-label-sm text-danger" x-on:click="removeItem('skills', index)">Remove</button>
                                </article>
                            </template>
                            <x-ui.button type="button" variant="secondary" size="sm" icon="plus" x-on:click="addItem('skills', { name: '', category: '', proficiency: '' })">Add Skill</x-ui.button>
                        </section>

                        <section x-show="activeStep === 4" x-transition.opacity class="mt-6 space-y-4">
                            <template x-for="(item, index) in projects" :key="index">
                                <article class="rounded-lg border border-border-light bg-surface-container-low p-5">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <input type="hidden" :name="`projects[${index}][id]`" x-model="item.id"><input type="hidden" :name="`projects[${index}][sort_order]`" x-model="item.sort_order"><input type="hidden" :name="`projects[${index}][is_visible]`" value="1">
                                        <div><label class="rh-label mb-2">Project Name</label><input class="rh-input" :name="`projects[${index}][name]`" x-model="item.name"></div>
                                        <div><label class="rh-label mb-2">Role</label><input class="rh-input" :name="`projects[${index}][role]`" x-model="item.role"></div>
                                        <div><label class="rh-label mb-2">Project URL</label><input class="rh-input" type="url" :name="`projects[${index}][url]`" x-model="item.url"></div>
                                        <div><label class="rh-label mb-2">Repository URL</label><input class="rh-input" type="url" :name="`projects[${index}][repository_url]`" x-model="item.repository_url"></div>
                                        <div class="sm:col-span-2"><label class="rh-label mb-2">Description</label><textarea class="rh-input min-h-28" :name="`projects[${index}][description]`" x-model="item.description"></textarea></div>
                                        <div class="sm:col-span-2"><label class="rh-label mb-2">Technologies</label><input class="rh-input" :name="`projects[${index}][technologies]`" x-model="item.technologies"></div>
                                    </div>
                                    <button type="button" class="mt-4 text-label-sm text-danger" x-on:click="removeItem('projects', index)">Remove project</button>
                                </article>
                            </template>
                            <x-ui.button type="button" variant="secondary" size="sm" icon="plus" x-on:click="addItem('projects', { name: '', role: '', url: '', repository_url: '', description: '', technologies: '' })">Add Project</x-ui.button>
                        </section>

                        <section x-show="activeStep === 5" x-transition.opacity class="mt-6 space-y-4">
                            <template x-for="(item, index) in languages" :key="index">
                                <article class="grid items-end gap-4 rounded-lg border border-border-light bg-surface-container-low p-4 sm:grid-cols-[1.4fr_1fr_auto]">
                                    <input type="hidden" :name="`languages[${index}][sort_order]`" x-model="item.sort_order"><input type="hidden" :name="`languages[${index}][is_visible]`" value="1">
                                    <div><label class="rh-label mb-2">Language</label><input class="rh-input" :name="`languages[${index}][name]`" x-model="item.name"></div>
                                    <div><label class="rh-label mb-2">Proficiency</label><select class="rh-input" :name="`languages[${index}][proficiency]`" x-model="item.proficiency"><option value="">Select</option><option>Basic</option><option>Conversational</option><option>Professional</option><option>Fluent</option><option>Native</option></select></div>
                                    <button type="button" class="min-h-11 px-2 text-label-sm text-danger" x-on:click="removeItem('languages', index)">Remove</button>
                                </article>
                            </template>
                            <x-ui.button type="button" variant="secondary" size="sm" icon="plus" x-on:click="addItem('languages', { name: '', proficiency: '' })">Add Language</x-ui.button>
                        </section>

                        <section x-show="activeStep === 6" x-transition.opacity class="mt-6">
                            <label class="rh-label mb-2" for="summary">Professional Summary</label>
                            <textarea id="summary" name="summary" class="rh-input min-h-56" x-model="summary" maxlength="3000"></textarea>
                            <div class="mt-2 flex justify-between text-[11px] text-on-surface-variant"><span>Focus on impact, strengths, and target role.</span><span x-text="`${summary.length}/3000`"></span></div>
                        </section>

                        <section x-show="activeStep === 7" x-transition.opacity class="mt-6 space-y-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-lg border border-border-light p-4"><p class="text-label-sm text-on-surface-variant">Personal Information</p><p class="mt-2 font-display text-label-md" x-text="profile.full_name || 'Not completed'"></p></div>
                                <div class="rounded-lg border border-border-light p-4"><p class="text-label-sm text-on-surface-variant">Education</p><p class="mt-2 font-display text-label-md" x-text="`${visibleItems('educations').filter(item => item.institution).length} entries`"></p></div>
                                <div class="rounded-lg border border-border-light p-4"><p class="text-label-sm text-on-surface-variant">Experience</p><p class="mt-2 font-display text-label-md" x-text="`${visibleItems('experiences').filter(item => item.company || item.position).length} entries`"></p></div>
                                <div class="rounded-lg border border-border-light p-4"><p class="text-label-sm text-on-surface-variant">Skills</p><p class="mt-2 font-display text-label-md" x-text="`${visibleItems('skills').filter(item => item.name).length} skills`"></p></div>
                                <div class="rounded-lg border border-border-light p-4"><p class="text-label-sm text-on-surface-variant">Projects</p><p class="mt-2 font-display text-label-md" x-text="`${visibleItems('projects').filter(item => item.name).length} projects`"></p></div>
                                <div class="rounded-lg border border-border-light p-4"><p class="text-label-sm text-on-surface-variant">Languages</p><p class="mt-2 font-display text-label-md" x-text="`${visibleItems('languages').filter(item => item.name).length} languages`"></p></div>
                            </div>
                            <div class="rounded-lg bg-primary/5 p-5"><p class="font-display text-label-md text-on-surface">Resume completeness</p><div class="mt-3 h-2 overflow-hidden rounded-full bg-surface-container"><div class="h-full rounded-full bg-primary transition-all" :style="`width: ${completionScore}%`"></div></div><p class="mt-2 text-label-sm text-on-surface-variant"><span x-text="completionScore"></span>% complete</p></div>
                        </section>
                    </div>

                    <footer class="resume-builder-footer sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-3 border-t border-border-light bg-white/95 px-4 py-3 backdrop-blur sm:px-5">
                        <span class="inline-flex items-center gap-2 text-label-sm text-on-surface-variant"><span class="h-2 w-2 rounded-full" :class="autosaveState === 'Save failed' ? 'bg-danger' : 'bg-success'"></span><span x-text="autosaveState === 'Ready' ? '{{ $resume ? 'All changes saved' : 'Save to enable autosave' }}' : autosaveState"></span></span>
                        <div class="ml-auto flex items-center gap-2">
                            <x-ui.button type="button" variant="white" size="sm" x-show="activeStep > 0" x-on:click="previousStep()">Previous</x-ui.button>
                            <x-ui.button type="submit" variant="white" size="sm">Save as Draft</x-ui.button>
                            <x-ui.button type="button" size="sm" iconAfter="arrow-right" x-show="activeStep < 7" x-on:click="nextStep()"><span x-text="`Next: ${stepLabel(activeStep + 1)}`">Next</span></x-ui.button>
                        </div>
                    </footer>
                </form>
            </section>

            <aside id="resume-live-preview" class="fixed inset-0 z-[70] overflow-y-auto bg-background p-4 xl:inset-auto xl:top-20 xl:sticky xl:self-start xl:z-auto xl:block xl:overflow-visible xl:bg-transparent xl:p-0" x-bind:class="previewOpen ? 'block' : 'hidden xl:block'">
                <div class="mb-2 flex items-center justify-between rounded-xl border border-border-light bg-white p-3 shadow-soft">
                    <div class="min-w-0 flex-1">
                        <label class="block text-[11px] text-on-surface-variant">Template</label>
                        <select form="resume-builder-form" name="template_id" class="mt-1 max-w-full border-0 bg-transparent p-0 font-display text-label-md text-on-surface focus:ring-0" x-model="template_id" x-on:change="queueAutosave()">
                            @foreach ($templateOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2" aria-label="Theme color">
                        @foreach (['#111827', '#153e75', '#0f7a5a', '#5b2be0', '#7f1d3a'] as $color)
                            <button type="button" class="h-6 w-6 rounded-full border-2 border-white shadow ring-offset-2" style="background-color: {{ $color }}" x-bind:class="theme.accent_color === '{{ $color }}' ? 'ring-2 ring-primary' : ''" x-on:click="theme.accent_color = '{{ $color }}'; queueAutosave()" aria-label="Use {{ $color }}"></button>
                        @endforeach
                    </div>
                    <button type="button" class="ml-3 rounded-md p-2 text-on-surface-variant xl:hidden" x-on:click="previewOpen = false" aria-label="Close preview"><x-ui.icon name="x-mark" /></button>
                </div>

                <div class="resume-preview-panel rounded-xl border border-border-light bg-white p-2 shadow-lift">
                    @livewire('live-resume-preview', ['resume' => $resume])
                    <div class="mt-2 flex items-center justify-between rounded-lg border border-border-light px-3 py-2 text-label-sm text-on-surface-variant"><span>Page 1 of 1</span><span>100%</span></div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>

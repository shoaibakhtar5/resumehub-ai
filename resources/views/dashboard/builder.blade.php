@php
    $resume?->loadMissing([
        'profile', 'summary', 'socialLinks', 'experiences', 'educations', 'projects', 'skills',
        'languages', 'certifications', 'awards', 'references', 'customSections.items', 'sections',
        'template', 'versions',
    ]);

    $settings = $resume?->settings ?? [];
    $storedTheme = old('theme', $settings['theme'] ?? []);
    $date = fn ($value) => $value ? $value->format('Y-m-d') : '';
    $rows = fn ($items, array $blank) => ($items ?? collect())->isEmpty() ? [$blank] : $items->values()->all();
    $selectedTemplate = old('template_id', $selectedTemplate ?? $resume?->template_id);

    $profile = old('profile', [
        'full_name' => $resume?->profile?->full_name ?? auth()->user()->name,
        'headline' => $resume?->profile?->headline ?? $resume?->target_role,
        'email' => $resume?->profile?->email ?? auth()->user()->email,
        'phone' => $resume?->profile?->phone ?? auth()->user()->phone,
        'website' => $resume?->profile?->website,
        'location' => $resume?->profile?->location,
        'photo_path' => $resume?->profile?->photo_path,
    ]);

    $mapRows = function ($items, callable $callback, array $blank) use ($rows) {
        return $rows($items?->map($callback), $blank);
    };

    $socialLinks = $mapRows($resume?->socialLinks, fn ($item) => [
        'id' => $item->id, 'platform' => $item->platform, 'label' => $item->label,
        'url' => $item->url, 'is_visible' => $item->is_visible, 'sort_order' => $item->sort_order,
    ], ['platform' => 'linkedin', 'label' => 'LinkedIn', 'url' => '', 'is_visible' => true, 'sort_order' => 0]);

    $experiences = $mapRows($resume?->experiences, fn ($item) => [
        'id' => $item->id, 'company' => $item->company, 'position' => $item->position,
        'employment_type' => $item->employment_type, 'location' => $item->location,
        'start_date' => $date($item->start_date), 'end_date' => $date($item->end_date),
        'is_current' => $item->is_current, 'description' => $item->description,
        'technologies' => implode(', ', $item->technologies ?? []), 'is_visible' => $item->is_visible,
        'sort_order' => $item->sort_order,
    ], ['company' => '', 'position' => '', 'employment_type' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '', 'technologies' => '', 'is_visible' => true, 'sort_order' => 0]);

    $educations = $mapRows($resume?->educations, fn ($item) => [
        'id' => $item->id, 'institution' => $item->institution, 'degree' => $item->degree,
        'field_of_study' => $item->field_of_study, 'location' => $item->location,
        'start_date' => $date($item->start_date), 'end_date' => $date($item->end_date),
        'is_current' => $item->is_current, 'grade' => $item->grade, 'description' => $item->description,
        'is_visible' => $item->is_visible, 'sort_order' => $item->sort_order,
    ], ['institution' => '', 'degree' => '', 'field_of_study' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'grade' => '', 'description' => '', 'is_visible' => true, 'sort_order' => 0]);

    $skills = $mapRows($resume?->skills, fn ($item) => [
        'name' => $item->name, 'category' => $item->pivot?->category,
        'proficiency' => $item->pivot?->proficiency, 'years_experience' => $item->pivot?->years_experience,
        'is_visible' => (bool) ($item->pivot?->is_visible ?? true), 'sort_order' => (int) ($item->pivot?->sort_order ?? 0),
    ], ['name' => '', 'category' => '', 'proficiency' => '', 'years_experience' => '', 'is_visible' => true, 'sort_order' => 0]);

    $projects = $mapRows($resume?->projects, fn ($item) => [
        'id' => $item->id, 'name' => $item->name, 'role' => $item->role, 'url' => $item->url,
        'repository_url' => $item->repository_url, 'start_date' => $date($item->start_date),
        'end_date' => $date($item->end_date), 'is_current' => $item->is_current,
        'description' => $item->description, 'technologies' => implode(', ', $item->technologies ?? []),
        'is_visible' => $item->is_visible, 'sort_order' => $item->sort_order,
    ], ['name' => '', 'role' => '', 'url' => '', 'repository_url' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '', 'technologies' => '', 'is_visible' => true, 'sort_order' => 0]);

    $languages = $mapRows($resume?->languages, fn ($item) => [
        'name' => $item->name, 'iso_code' => $item->iso_code, 'proficiency' => $item->pivot?->proficiency,
        'is_visible' => (bool) ($item->pivot?->is_visible ?? true), 'sort_order' => (int) ($item->pivot?->sort_order ?? 0),
    ], ['name' => '', 'iso_code' => '', 'proficiency' => '', 'is_visible' => true, 'sort_order' => 0]);

    $certifications = $mapRows($resume?->certifications, fn ($item) => [
        'id' => $item->id, 'name' => $item->name, 'issuer' => $item->issuer,
        'issued_at' => $date($item->issued_at), 'expires_at' => $date($item->expires_at),
        'credential_id' => $item->credential_id, 'credential_url' => $item->credential_url,
        'description' => $item->description, 'is_visible' => $item->is_visible, 'sort_order' => $item->sort_order,
    ], ['name' => '', 'issuer' => '', 'issued_at' => '', 'expires_at' => '', 'credential_id' => '', 'credential_url' => '', 'description' => '', 'is_visible' => true, 'sort_order' => 0]);

    $awards = $mapRows($resume?->awards, fn ($item) => [
        'id' => $item->id, 'title' => $item->title, 'issuer' => $item->issuer,
        'awarded_at' => $date($item->awarded_at), 'description' => $item->description,
        'is_visible' => $item->is_visible, 'sort_order' => $item->sort_order,
    ], ['title' => '', 'issuer' => '', 'awarded_at' => '', 'description' => '', 'is_visible' => true, 'sort_order' => 0]);

    $references = $mapRows($resume?->references, fn ($item) => [
        'id' => $item->id, 'name' => $item->name, 'title' => $item->title, 'company' => $item->company,
        'email' => $item->email, 'phone' => $item->phone, 'relationship' => $item->relationship,
        'available_on_request' => $item->available_on_request, 'is_visible' => $item->is_visible,
        'sort_order' => $item->sort_order,
    ], ['name' => '', 'title' => '', 'company' => '', 'email' => '', 'phone' => '', 'relationship' => '', 'available_on_request' => false, 'is_visible' => true, 'sort_order' => 0]);

    $sectionDefaults = collect([
        ['section_key' => 'personal', 'title' => 'Personal Information', 'is_visible' => true],
        ['section_key' => 'summary', 'title' => 'Professional Summary', 'is_visible' => true],
        ['section_key' => 'experience', 'title' => 'Experience', 'is_visible' => true],
        ['section_key' => 'education', 'title' => 'Education', 'is_visible' => true],
        ['section_key' => 'skills', 'title' => 'Skills', 'is_visible' => true],
        ['section_key' => 'projects', 'title' => 'Projects', 'is_visible' => true],
        ['section_key' => 'languages', 'title' => 'Languages', 'is_visible' => true],
        ['section_key' => 'certifications', 'title' => 'Certifications', 'is_visible' => true],
        ['section_key' => 'awards', 'title' => 'Awards', 'is_visible' => true],
        ['section_key' => 'references', 'title' => 'References', 'is_visible' => false],
    ]);
    $storedSections = $resume?->sections?->keyBy('section_key') ?? collect();
    $sections = $sectionDefaults->map(function ($section, $index) use ($storedSections) {
        $stored = $storedSections->get($section['section_key']);
        return [
            ...$section,
            'title' => $stored?->title ?? $section['title'],
            'is_visible' => $stored?->is_visible ?? $section['is_visible'],
            'sort_order' => $stored?->sort_order ?? $index,
            'settings' => array_merge(['locked' => false, 'column' => 'main'], $stored?->settings ?? []),
        ];
    })->sortBy('sort_order')->values()->all();

    $theme = array_merge([
        'accent_color' => '#3155e7', 'secondary_color' => '#142845', 'heading_font' => 'Poppins',
        'body_font' => 'Inter', 'font_pairing' => 'modern', 'font_scale' => 100, 'density' => 'balanced',
        'page_size' => 'a4', 'layout' => 'two-column', 'sidebar_width' => 34, 'photo_position' => 'center',
        'section_spacing' => 'medium', 'content_width' => 'standard', 'page_background' => '#ffffff',
        'dividers' => true, 'shadow' => true, 'header_color' => '#17243b', 'header_scale' => 100,
    ], $storedTheme);

    $builderData = [
        'formId' => 'resume-builder-form', 'csrfToken' => csrf_token(),
        'autosaveUrl' => $resume ? route('resumes.autosave', $resume) : null,
        'templateThemeUrl' => $resume ? route('resumes.template-theme', $resume) : null,
        'aiUrl' => route('ai.generate'), 'resumeId' => $resume?->id, 'activePanel' => 'elements',
        'activeSection' => 'personal', 'designTab' => 'design', 'mobilePane' => 'canvas',
        'autosaveState' => $resume ? 'Saved' : 'Save to enable autosave',
        'completionScore' => $resume?->completion_score ?? 0,
        'latestAtsScore' => $latestReport ? (int) round($latestReport->ats_score) : null,
        'title' => old('title', $resume?->title ?? auth()->user()->name.' Resume'),
        'target_role' => old('target_role', $resume?->target_role ?? ''),
        'target_company' => old('target_company', $resume?->target_company ?? ''),
        'template_id' => $selectedTemplate, 'summary' => old('summary', $resume?->summary?->content ?? $settings['summary'] ?? ''),
        'profile' => $profile, 'social_links' => $socialLinks, 'experiences' => $experiences,
        'educations' => $educations, 'skills' => $skills, 'projects' => $projects,
        'languages' => $languages, 'certifications' => $certifications, 'awards' => $awards,
        'references' => $references, 'sections' => $sections, 'theme' => $theme, 'settings' => $settings,
        'photoPreview' => $profile['photo_path'] ?? null, 'zoom' => (int) ($settings['builder_zoom'] ?? 100),
        'device' => 'desktop', 'page' => 1, 'pageCount' => 1,
    ];

    $sectionMeta = [
        'personal' => ['Personal Information', 'user'], 'summary' => ['Professional Summary', 'document-text'],
        'experience' => ['Experience', 'briefcase'], 'education' => ['Education', 'academic-cap'],
        'skills' => ['Skills', 'link'], 'projects' => ['Projects', 'folder'],
        'languages' => ['Languages', 'globe-alt'], 'certifications' => ['Certifications', 'check'],
        'awards' => ['Awards', 'presentation-chart-line'], 'references' => ['References', 'identification'],
    ];
@endphp

<x-builder-layout title="Resume Builder">
    <div x-data="resumeBuilder(@js($builderData))" x-init="init()" class="resume-studio" x-on:keydown.window="handleShortcut($event)" x-on:ai-suggestion-applied.window="applyAiOutputFromEvent($event.detail)">
        <header class="resume-studio-topbar">
            <div class="resume-studio-brand"><x-brand :href="route('dashboard')" /></div>
            <div class="flex min-w-0 flex-1 items-center gap-3 border-l border-slate-200 px-4">
                <a href="{{ route('resumes.index') }}" class="studio-icon-button" aria-label="Back to resumes"><x-ui.icon name="arrow-left-on-rectangle" class="h-5 w-5" /></a>
                <input type="text" x-model="title" class="min-w-0 max-w-sm flex-1 border-0 bg-transparent p-0 font-display text-base font-semibold text-slate-900 focus:ring-0" aria-label="Resume title">
                <span class="hidden items-center gap-1.5 text-xs font-medium sm:flex" :class="autosaveState === 'Save failed' ? 'text-red-600' : 'text-emerald-600'">
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span><span x-text="autosaveState"></span>
                </span>
            </div>
            <div class="flex items-center gap-2 px-3">
                <button type="button" class="studio-icon-button" x-on:click="undo()" :disabled="!canUndo" title="Undo"><x-ui.icon name="arrow-path" class="h-4 w-4" /></button>
                <button type="button" class="studio-icon-button" x-on:click="redo()" :disabled="!canRedo" title="Redo"><x-ui.icon name="arrow-path" class="h-4 w-4 -scale-x-100" /></button>
                <button type="button" class="studio-action-button hidden sm:inline-flex" x-on:click="toggleFullscreen()"><x-ui.icon name="eye" class="h-4 w-4" /> Preview</button>
                @if ($resume)
                    <button type="submit" form="resume-builder-form" class="studio-action-button hidden lg:inline-flex">Save version</button>
                    <form method="POST" action="{{ route('resumes.download', $resume) }}">@csrf<input type="hidden" name="format" value="pdf"><button class="studio-primary-button"><x-ui.icon name="arrow-down-tray" class="h-4 w-4" /> Download</button></form>
                @else
                    <button type="submit" form="resume-builder-form" class="studio-primary-button">Save resume</button>
                @endif
                <button type="button" class="studio-icon-button" x-on:click="designTab = 'settings'" aria-label="Settings"><x-ui.icon name="cog-6-tooth" class="h-5 w-5" /></button>
            </div>
        </header>

        @if (session('status'))<div class="resume-studio-notice">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="resume-studio-notice border-red-200 bg-red-50 text-red-700">Please review the highlighted fields before saving.</div>@endif

        <form id="resume-builder-form" method="POST" enctype="multipart/form-data" action="{{ $resume ? route('resumes.update', $resume) : route('resumes.store') }}" class="contents" novalidate x-on:input.debounce.350ms="queueChange()" x-on:change="queueChange()">
            @csrf
            @if ($resume) @method('PATCH') @endif
            <input type="hidden" name="title" :value="title"><input type="hidden" name="target_role" :value="target_role"><input type="hidden" name="target_company" :value="target_company"><input type="hidden" name="template_id" :value="template_id">
            @foreach (['social_links','experiences','educations','projects','skills','languages','certifications','awards','references','sections'] as $collection)<input type="hidden" name="present_collections[]" value="{{ $collection }}">@endforeach
            @foreach (array_keys($theme) as $key)
                @if (in_array($key, ['dividers', 'shadow'], true))
                    <input type="hidden" name="theme[{{ $key }}]" :value="truthy(theme.{{ $key }}) ? 1 : 0">
                @else
                    <input type="hidden" name="theme[{{ $key }}]" :value="theme.{{ $key }}">
                @endif
            @endforeach
            <template x-for="(section,index) in sections" :key="`section-field-${section.section_key}`">
                <div class="hidden">
                    <input type="hidden" :name="`sections[${index}][section_key]`" x-model="section.section_key">
                    <input type="hidden" :name="`sections[${index}][title]`" x-model="section.title">
                    <input type="hidden" :name="`sections[${index}][is_visible]`" :value="truthy(section.is_visible) ? 1 : 0">
                    <input type="hidden" :name="`sections[${index}][sort_order]`" x-model="section.sort_order">
                    <input type="hidden" :name="`sections[${index}][settings][locked]`" :value="truthy(section.settings?.locked) ? 1 : 0">
                    <input type="hidden" :name="`sections[${index}][settings][column]`" x-model="section.settings.column">
                    <input type="hidden" :name="`sections[${index}][settings][font_family]`" :value="section.settings?.font_family || ''">
                    <input type="hidden" :name="`sections[${index}][settings][font_scale]`" :value="section.settings?.font_scale || ''">
                </div>
            </template>

            <aside class="resume-studio-left" :class="mobilePane === 'editor' ? 'is-mobile-active' : ''">
                <nav class="studio-tabs" aria-label="Editor tools">
                    @foreach ([['elements','Elements'],['sections','Sections'],['ai','AI Tools']] as [$tab,$label])
                        <button type="button" :class="activePanel === '{{ $tab }}' && 'is-active'" x-on:click="activePanel = '{{ $tab }}'">{{ $label }}</button>
                    @endforeach
                </nav>

                <div class="studio-panel-scroll">
                    <div x-show="activePanel === 'elements'" class="p-4">
                        <p class="studio-kicker">Content blocks</p>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach ($sectionMeta as $key => [$label,$icon])
                                <button type="button" class="studio-element-card" x-on:click="selectSection('{{ $key }}')"><x-ui.icon :name="$icon" class="h-5 w-5" /><span>{{ $label }}</span></button>
                            @endforeach
                        </div>
                        <div class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50/70 p-3 text-xs text-slate-600"><strong class="block text-indigo-700">Live editing</strong>Select a block, edit its real saved data, and compare the result on the canvas.</div>
                    </div>

                    <div x-show="activePanel === 'sections'" class="p-4">
                        <div class="mb-3 flex items-center justify-between"><p class="studio-kicker">Resume sections</p><span class="text-[11px] text-slate-400">Drag to reorder</span></div>
                        <div class="space-y-2">
                            <template x-for="(section, index) in sections" :key="section.section_key">
                                <div draggable="true" x-on:dragstart="startSectionDrag(index)" x-on:dragover.prevent x-on:drop="dropSection(index)" class="studio-section-row" :class="activeSection === section.section_key && 'is-active'">
                                    <button type="button" class="cursor-grab text-slate-400" aria-label="Reorder"><x-ui.icon name="bars-3" class="h-4 w-4" /></button>
                                    <button type="button" class="min-w-0 flex-1 truncate text-left" x-text="section.title" x-on:click="selectSection(section.section_key)"></button>
                                    <button type="button" class="text-slate-400 hover:text-indigo-600" x-on:click="toggleSectionLock(section)" :title="section.settings?.locked ? 'Unlock' : 'Lock'"><x-ui.icon name="lock-closed" class="h-4 w-4" /></button>
                                    <button type="button" class="text-slate-400 hover:text-indigo-600" x-on:click="section.is_visible = !truthy(section.is_visible); queueChange()" :title="truthy(section.is_visible) ? 'Hide' : 'Show'"><x-ui.icon name="eye" class="h-4 w-4" /></button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="activePanel === 'ai'" class="p-4">
                        @livewire('ai-assistant-panel', ['resumeId' => $resume?->id])
                    </div>

                    <div class="border-t border-slate-200 p-4" :class="isSectionLocked(activeSection) && 'pointer-events-none opacity-60'">
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <div><p class="font-display text-sm font-semibold text-slate-900" x-text="sectionTitle(activeSection)"></p><p class="mt-0.5 text-[11px] text-slate-500">Changes update the canvas instantly.</p></div>
                            <button type="button" class="studio-ai-mini" x-on:click="activePanel = 'ai'"><x-ui.icon name="sparkles" class="h-3.5 w-3.5" /> AI</button>
                        </div>

                        <section x-show="activeSection === 'personal'" class="studio-editor-fields">
                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200" x-show="selectedType === 'field' || selectedType === 'image'">
                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="deselectElement()">← Show all details</button>
                            </div>
                            <div x-show="selectedType === 'image' || selectedType === 'none' || selectedType === 'page'" class="mb-4">
                                <button type="button" class="group relative mx-auto block h-20 w-20 overflow-hidden rounded-full border-4 border-indigo-50 bg-indigo-100" x-on:click="$refs.photoInput.click()"><template x-if="photoPreview"><img :src="photoPreview" class="h-full w-full object-cover" alt="Profile photo"></template><template x-if="!photoPreview"><span class="flex h-full w-full items-center justify-center text-2xl font-bold text-indigo-700" x-text="(profile.full_name || 'R').charAt(0)"></span></template><span class="absolute inset-x-0 bottom-0 bg-slate-900/60 py-1 text-[9px] text-white">Change photo</span></button>
                                <input x-ref="photoInput" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="sr-only" x-on:change="handlePhoto($event)">
                            </div>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'field' && selectedFieldName === 'full_name')">Full name<input name="profile[full_name]" x-model="profile.full_name"></label>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'field' && selectedFieldName === 'headline')">Job title<input x-model="target_role"></label>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'field' && selectedFieldName === 'headline')">Headline<input name="profile[headline]" x-model="profile.headline"></label>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'field' && selectedFieldName === 'email')">Email<input type="email" name="profile[email]" x-model="profile.email"></label>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'field' && selectedFieldName === 'phone')">Phone<input name="profile[phone]" x-model="profile.phone"></label>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'field' && selectedFieldName === 'location')">Location<input name="profile[location]" x-model="profile.location"></label>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'field' && selectedFieldName === 'website')">Website<input name="profile[website]" inputmode="url" x-model="profile.website"></label>
                            <label x-show="selectedType === 'none' || selectedType === 'page' || (selectedType === 'item' && selectedItemCollection === 'social_links')">LinkedIn<input :name="`social_links[0][url]`" inputmode="url" x-model="social_links[0].url"></label>
                            <input type="hidden" name="social_links[0][id]" x-model="social_links[0].id"><input type="hidden" name="social_links[0][platform]" value="linkedin"><input type="hidden" name="social_links[0][label]" value="LinkedIn"><input type="hidden" name="social_links[0][is_visible]" value="1"><input type="hidden" name="social_links[0][sort_order]" value="0">
                        </section>

                        <section x-show="activeSection === 'summary'" class="studio-editor-fields"><label>Professional summary<textarea name="summary" rows="8" x-model="summary"></textarea></label></section>

                        <section x-show="activeSection === 'experience'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'experiences'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select an item below to edit its content:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in experiences" :key="item.id || `exp-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `item.experience.${index}`, 'experience', index, 'experiences')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.position || '(Blank Position)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.company || '(Blank Company)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('experiences', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('experiences',{company:'',position:'',location:'',start_date:'',end_date:'',description:'',technologies:''})">+ Add experience</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'experiences'">
                                <template x-for="(item, index) in experiences" :key="item.id || `exp-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.experience', 'experience')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Item ${index + 1} of ${experiences.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`experiences[${index}][id]`" x-model="item.id">
                                            <input type="hidden" :name="`experiences[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`experiences[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Position<input :name="`experiences[${index}][position]`" x-model="item.position"></label>
                                            <label>Company<input :name="`experiences[${index}][company]`" x-model="item.company"></label>
                                            <div class="studio-date-grid">
                                                <label>Start<input type="date" :name="`experiences[${index}][start_date]`" x-model="item.start_date"></label>
                                                <label>End<input type="date" :name="`experiences[${index}][end_date]`" x-model="item.end_date"></label>
                                            </div>
                                            <label>Location<input :name="`experiences[${index}][location]`" x-model="item.location"></label>
                                            <label>Achievements<textarea rows="5" :name="`experiences[${index}][description]`" x-model="item.description"></textarea></label>
                                            <label>Technologies<input :name="`experiences[${index}][technologies]`" x-model="item.technologies"></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('experiences', index); selectElement('section', 'section.experience', 'experience')">Remove experience</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section x-show="activeSection === 'education'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'educations'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select an item below to edit its content:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in educations" :key="item.id || `edu-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `item.education.${index}`, 'education', index, 'educations')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.degree || '(Blank Degree)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.institution || '(Blank Institution)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('educations', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('educations',{institution:'',degree:'',field_of_study:'',start_date:'',end_date:'',description:''})">+ Add education</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'educations'">
                                <template x-for="(item, index) in educations" :key="item.id || `edu-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.education', 'education')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Item ${index + 1} of ${educations.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`educations[${index}][id]`" x-model="item.id">
                                            <input type="hidden" :name="`educations[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`educations[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Degree<input :name="`educations[${index}][degree]`" x-model="item.degree"></label>
                                            <label>Institution<input :name="`educations[${index}][institution]`" x-model="item.institution"></label>
                                            <label>Field of study<input :name="`educations[${index}][field_of_study]`" x-model="item.field_of_study"></label>
                                            <div class="studio-date-grid">
                                                <label>Start<input type="date" :name="`educations[${index}][start_date]`" x-model="item.start_date"></label>
                                                <label>End<input type="date" :name="`educations[${index}][end_date]`" x-model="item.end_date"></label>
                                            </div>
                                            <label>Description<textarea rows="4" :name="`educations[${index}][description]`" x-model="item.description"></textarea></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('educations', index); selectElement('section', 'section.education', 'education')">Remove education</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section x-show="activeSection === 'skills'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'skills'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select a skill below to edit:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in skills" :key="`skill-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `tag.skills.${index}`, 'skills', index, 'skills')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.name || '(Blank Skill)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.proficiency || '(No proficiency)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('skills', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('skills',{name:'',proficiency:''})">+ Add skill</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'skills'">
                                <template x-for="(item, index) in skills" :key="`skill-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters compact">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.skills', 'skills')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Skill ${index + 1} of ${skills.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`skills[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`skills[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Skill<input :name="`skills[${index}][name]`" x-model="item.name"></label>
                                            <label>Proficiency<select :name="`skills[${index}][proficiency]`" x-model="item.proficiency"><option value="">Select</option><option>Beginner</option><option>Intermediate</option><option>Advanced</option><option>Expert</option></select></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('skills', index); selectElement('section', 'section.skills', 'skills')">Remove</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section x-show="activeSection === 'projects'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'projects'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select a project below to edit:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in projects" :key="item.id || `proj-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `item.projects.${index}`, 'projects', index, 'projects')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.name || '(Blank Project)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.role || '(Blank Role)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('projects', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('projects',{name:'',role:'',description:'',technologies:'',url:''})">+ Add project</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'projects'">
                                <template x-for="(item, index) in projects" :key="item.id || `proj-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.projects', 'projects')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Project ${index + 1} of ${projects.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`projects[${index}][id]`" x-model="item.id">
                                            <input type="hidden" :name="`projects[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`projects[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Project name<input :name="`projects[${index}][name]`" x-model="item.name"></label>
                                            <label>Role<input :name="`projects[${index}][role]`" x-model="item.role"></label>
                                            <label>Description<textarea rows="5" :name="`projects[${index}][description]`" x-model="item.description"></textarea></label>
                                            <label>Technologies<input :name="`projects[${index}][technologies]`" x-model="item.technologies"></label>
                                            <label>Project URL<input :name="`projects[${index}][url]`" inputmode="url" x-model="item.url"></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('projects', index); selectElement('section', 'section.projects', 'projects')">Remove project</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section x-show="activeSection === 'languages'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'languages'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select a language below to edit:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in languages" :key="`lang-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `tag.languages.${index}`, 'languages', index, 'languages')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.name || '(Blank Language)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.proficiency || '(No proficiency)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('languages', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('languages',{name:'',proficiency:''})">+ Add language</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'languages'">
                                <template x-for="(item, index) in languages" :key="`lang-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters compact">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.languages', 'languages')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Language ${index + 1} of ${languages.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`languages[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`languages[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Language<input :name="`languages[${index}][name]`" x-model="item.name"></label>
                                            <label>Proficiency<input :name="`languages[${index}][proficiency]`" x-model="item.proficiency"></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('languages', index); selectElement('section', 'section.languages', 'languages')">Remove</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section x-show="activeSection === 'certifications'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'certifications'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select a certification below to edit:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in certifications" :key="item.id || `cert-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `item.certifications.${index}`, 'certifications', index, 'certifications')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.name || '(Blank Certification)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.issuer || '(Blank Issuer)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('certifications', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('certifications',{name:'',issuer:'',issued_at:'',credential_url:''})">+ Add certification</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'certifications'">
                                <template x-for="(item, index) in certifications" :key="item.id || `cert-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.certifications', 'certifications')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Cert ${index + 1} of ${certifications.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`certifications[${index}][id]`" x-model="item.id">
                                            <input type="hidden" :name="`certifications[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`certifications[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Certification<input :name="`certifications[${index}][name]`" x-model="item.name"></label>
                                            <label>Issuer<input :name="`certifications[${index}][issuer]`" x-model="item.issuer"></label>
                                            <label>Issued<input type="date" :name="`certifications[${index}][issued_at]`" x-model="item.issued_at"></label>
                                            <label>Credential URL<input :name="`certifications[${index}][credential_url]`" inputmode="url" x-model="item.credential_url"></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('certifications', index); selectElement('section', 'section.certifications', 'certifications')">Remove</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section x-show="activeSection === 'awards'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'awards'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select an award below to edit:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in awards" :key="item.id || `award-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `item.awards.${index}`, 'awards', index, 'awards')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.title || '(Blank Award)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.issuer || '(Blank Issuer)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('awards', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('awards',{title:'',issuer:'',awarded_at:'',description:''})">+ Add award</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'awards'">
                                <template x-for="(item, index) in awards" :key="item.id || `award-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.awards', 'awards')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Award ${index + 1} of ${awards.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`awards[${index}][id]`" x-model="item.id">
                                            <input type="hidden" :name="`awards[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`awards[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Award<input :name="`awards[${index}][title]`" x-model="item.title"></label>
                                            <label>Issuer<input :name="`awards[${index}][issuer]`" x-model="item.issuer"></label>
                                            <label>Date<input type="date" :name="`awards[${index}][awarded_at]`" x-model="item.awarded_at"></label>
                                            <label>Description<textarea rows="4" :name="`awards[${index}][description]`" x-model="item.description"></textarea></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('awards', index); selectElement('section', 'section.awards', 'awards')">Remove</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>

                        <section x-show="activeSection === 'references'">
                            <div x-show="selectedType !== 'item' || selectedItemCollection !== 'references'" class="space-y-3">
                                <div class="text-xs text-slate-500 mb-2">Select a reference below to edit:</div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in references" :key="item.id || `ref-row-${index}`">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white hover:border-blue-500 cursor-pointer"
                                             x-on:click="selectElement('item', `item.references.${index}`, 'references', index, 'references')">
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-xs text-slate-800" x-text="item.name || '(Blank Name)'"></div>
                                                <div class="text-[11px] text-slate-500" x-text="item.company || '(Blank Company)'"></div>
                                            </div>
                                            <div class="flex items-center gap-1" x-on:click.stop>
                                                <button type="button" class="p-1 text-slate-400 hover:text-red-500" x-on:click="removeItem('references', index)">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" class="studio-add w-full" x-on:click="addItem('references',{name:'',title:'',company:'',email:''})">+ Add reference</button>
                            </div>
                            <div x-show="selectedType === 'item' && selectedItemCollection === 'references'">
                                <template x-for="(item, index) in references" :key="item.id || `ref-edit-${index}`">
                                    <div x-show="selectedItemIndex === index" class="studio-repeaters">
                                        <article class="p-0 border-none bg-transparent">
                                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                                                <button type="button" class="text-xs text-blue-600 font-semibold hover:underline" x-on:click="selectElement('section', 'section.references', 'references')">← Back to list</button>
                                                <span class="text-xs text-slate-400" x-text="`Reference ${index + 1} of ${references.length}`"></span>
                                            </div>
                                            <input type="hidden" :name="`references[${index}][id]`" x-model="item.id">
                                            <input type="hidden" :name="`references[${index}][sort_order]`" x-model="item.sort_order">
                                            <input type="hidden" :name="`references[${index}][is_visible]`" x-model="item.is_visible">
                                            <label>Name<input :name="`references[${index}][name]`" x-model="item.name"></label>
                                            <label>Title<input :name="`references[${index}][title]`" x-model="item.title"></label>
                                            <label>Company<input :name="`references[${index}][company]`" x-model="item.company"></label>
                                            <label>Email<input type="email" :name="`references[${index}][email]`" x-model="item.email"></label>
                                            <button type="button" class="studio-remove" x-on:click="removeItem('references', index); selectElement('section', 'section.references', 'references')">Remove</button>
                                        </article>
                                    </div>
                                </template>
                            </div>
                        </section>
                    </div>
                </div>
            </aside>

            <main class="resume-studio-canvas" :class="[deviceClass, mobilePane === 'canvas' ? 'is-mobile-active' : '']">
                <div class="studio-canvas-toolbar">
                    <label class="studio-select wide"><x-ui.icon name="squares-2x2" class="h-4 w-4" /><span>Template</span><select x-model="template_id"><option value="">Modern Professional</option>@foreach ($templates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach</select></label>
                    <label class="studio-select"><select x-model="currentFont" x-on:change="queueChange()">@foreach (['Inter','Roboto','Lato','Poppins','Merriweather'] as $font)<option>{{ $font }}</option>@endforeach</select></label>
                    <div class="studio-toolbar-group"><button type="button" x-on:click="adjustFont(-5)">A−</button><span x-text="`${currentFontScale}%`"></span><button type="button" x-on:click="adjustFont(5)">A+</button></div>
                    <div class="ml-auto hidden items-center gap-1 sm:flex"><button type="button" class="studio-icon-button" x-on:click="device='desktop'" title="Desktop"><x-ui.icon name="presentation-chart-line" class="h-4 w-4" /></button><button type="button" class="studio-icon-button" x-on:click="device='tablet'" title="Tablet"><x-ui.icon name="squares-2x2" class="h-4 w-4" /></button><button type="button" class="studio-icon-button" x-on:click="device='mobile'" title="Mobile"><x-ui.icon name="identification" class="h-4 w-4" /></button></div>
                </div>

                <div class="studio-canvas-viewport" x-ref="canvasViewport">
                    <!-- studio-page-stage-wrapper: collapses to scaled dimensions so scroll works correctly -->
                    <div class="studio-page-stage-wrapper" x-ref="pageStageWrapper">
                        <!-- studio-page-stage: always 794px wide, CSS-scaled for zoom -->
                        <div class="studio-page-stage" x-ref="pageStage">
                            <div class="studio-page-selection" :class="{ 'has-selection': activeSection !== 'personal', 'without-shadow': !truthy(theme.shadow) }" x-on:click="handleCanvasClick($event)" x-ref="pageSelection">
                            @livewire('live-resume-preview', ['resume' => $resume])

                            {{-- Canva-style selection ring + dark floating action bar --}}
                            <div id="selection-overlay" style="display:none;">
                                {{-- Dark action toolbar --}}
                                <div x-on:click.stop>
                                    {{-- Move Up --}}
                                    <template x-if="selectedType === 'section' || selectedType === 'item'">
                                        <button type="button" title="Move Up" x-on:click="moveSelection('up')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                    </template>
                                    {{-- Move Down --}}
                                    <template x-if="selectedType === 'section' || selectedType === 'item'">
                                        <button type="button" title="Move Down" x-on:click="moveSelection('down')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </template>
                                    {{-- Separator --}}
                                    <template x-if="selectedType === 'item'">
                                        <span style="width:1px;height:16px;background:rgba(255,255,255,0.12);margin:0 2px;"></span>
                                    </template>
                                    {{-- Duplicate --}}
                                    <template x-if="selectedType === 'item'">
                                        <button type="button" title="Duplicate" x-on:click="duplicateSelection()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                        </button>
                                    </template>
                                    {{-- Delete --}}
                                    <template x-if="selectedType === 'section' || selectedType === 'item'">
                                        <button type="button" title="Delete" class="danger" x-on:click="deleteSelection()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </template>
                                    {{-- AI --}}
                                    <template x-if="selectedType === 'section' && selectedSectionKey === 'summary'">
                                        <button type="button" title="AI Improve" class="ai-btn" x-on:click="triggerAiAction()">✨ AI</button>
                                    </template>
                                </div>
                            </div>
                        </div>{{-- /.studio-page-selection --}}
                    </div>{{-- /.studio-page-stage --}}
                </div>{{-- /.studio-page-stage-wrapper --}}
                </div>{{-- /.studio-canvas-viewport --}}

                <footer class="studio-canvas-footer">
                    <button type="button" class="studio-action-button" x-on:click="designTab='design'; mobilePane='design'"><x-ui.icon name="squares-2x2" class="h-4 w-4" /> Templates</button>
                    <div class="flex items-center gap-1"><button type="button" class="studio-icon-button" x-on:click="setPage(page-1)">‹</button><span class="min-w-20 text-center text-xs font-medium">Page <span x-text="page"></span> of <span x-text="pageCount"></span></span><button type="button" class="studio-icon-button" x-on:click="setPage(page+1)">›</button></div>
                    <div class="flex items-center gap-1"><button type="button" class="studio-icon-button" x-on:click="setZoom(zoom-10)">−</button><span class="min-w-12 text-center text-xs" x-text="`${zoom}%`"></span><button type="button" class="studio-icon-button" x-on:click="setZoom(zoom+10)">+</button><button type="button" class="studio-icon-button" x-on:click="toggleFullscreen()"><x-ui.icon name="eye" class="h-4 w-4" /></button></div>
                </footer>
            </main>

            <aside class="resume-studio-right" :class="mobilePane === 'design' ? 'is-mobile-active' : ''">
                <nav class="studio-tabs"><button type="button" :class="designTab === 'design' && 'is-active'" x-on:click="designTab='design'">Design</button><button type="button" :class="designTab === 'page' && 'is-active'" x-on:click="designTab='page'">Page</button><button type="button" :class="designTab === 'settings' && 'is-active'" x-on:click="designTab='settings'">Settings</button></nav>
                <div class="studio-panel-scroll p-4">
                    <div x-show="designTab === 'design'" class="space-y-6">
                        <!-- Element Text Styles -->
                        <section class="studio-control-section" x-show="selectedType === 'field'">
                            <p class="studio-kicker">Text styling</p>
                            <label>Font family
                                <select x-model="theme.styles[selectedKey].font_family" x-on:change="queueChange()">
                                    <option value="">Default headings/body</option>
                                    @foreach (['Poppins','Inter','Roboto','Lato','Merriweather'] as $font)
                                        <option>{{ $font }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Font size
                                <select x-model="theme.styles[selectedKey].font_size" x-on:change="queueChange()">
                                    <option value="">Default</option>
                                    @foreach (['9px','10px','11px','12px','13px','14px','16px','18px','20px','24px','28px','32px'] as $sz)
                                        <option value="{{ $sz }}">{{ $sz }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Alignment
                                <select x-model="theme.styles[selectedKey].text_align" x-on:change="queueChange()">
                                    <option value="">Default</option>
                                    <option value="left">Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                    <option value="justify">Justified</option>
                                </select>
                            </label>
                            <label>Letter spacing
                                <select x-model="theme.styles[selectedKey].letter_spacing" x-on:change="queueChange()">
                                    <option value="">Default</option>
                                    <option value="normal">Normal</option>
                                    <option value="0.05em">0.05em</option>
                                    <option value="0.1em">0.1em</option>
                                    <option value="0.15em">0.15em</option>
                                </select>
                            </label>
                            <label>Line height
                                <select x-model="theme.styles[selectedKey].line_height" x-on:change="queueChange()">
                                    <option value="">Default</option>
                                    <option value="1">1.0</option>
                                    <option value="1.2">1.2</option>
                                    <option value="1.4">1.4</option>
                                    <option value="1.6">1.6</option>
                                </select>
                            </label>
                            <label>Text Color
                                <input type="color" x-model="theme.styles[selectedKey].color" x-on:input.debounce.150ms="queueChange()">
                            </label>
                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <button type="button" class="px-2 py-1.5 border border-slate-200 rounded text-xs font-bold" 
                                        :class="theme.styles[selectedKey].font_weight === 'bold' ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'bg-white text-slate-700'"
                                        x-on:click="theme.styles[selectedKey].font_weight = (theme.styles[selectedKey].font_weight === 'bold' ? 'normal' : 'bold'); queueChange()">B</button>
                                <button type="button" class="px-2 py-1.5 border border-slate-200 rounded text-xs italic" 
                                        :class="theme.styles[selectedKey].italic ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'bg-white text-slate-700'"
                                        x-on:click="theme.styles[selectedKey].italic = !theme.styles[selectedKey].italic; queueChange()">I</button>
                                <button type="button" class="px-2 py-1.5 border border-slate-200 rounded text-xs underline" 
                                        :class="theme.styles[selectedKey].underline ? 'bg-indigo-50 border-indigo-600 text-indigo-700' : 'bg-white text-slate-700'"
                                        x-on:click="theme.styles[selectedKey].underline = !theme.styles[selectedKey].underline; queueChange()">U</button>
                            </div>
                        </section>

                        <!-- Element Image Styles -->
                        <section class="studio-control-section" x-show="selectedType === 'image'">
                            <p class="studio-kicker">Image styling</p>
                            <label>Width (px)
                                <input type="range" min="40" max="250" step="5" x-model="theme.styles[selectedKey].width" x-on:input.debounce.150ms="queueChange()">
                                <span x-text="theme.styles[selectedKey].width || 'Default'"></span>
                            </label>
                            <label>Height (px)
                                <input type="range" min="40" max="250" step="5" x-model="theme.styles[selectedKey].height" x-on:input.debounce.150ms="queueChange()">
                                <span x-text="theme.styles[selectedKey].height || 'Default'"></span>
                            </label>
                            <label>Border Radius
                                <select x-model="theme.styles[selectedKey].border_radius" x-on:change="queueChange()">
                                    <option value="">Default</option>
                                    <option value="0px">Square (0px)</option>
                                    <option value="4px">Soft (4px)</option>
                                    <option value="8px">Rounded (8px)</option>
                                    <option value="50%">Circle (50%)</option>
                                </select>
                            </label>
                            <label>Opacity
                                <input type="range" min="10" max="100" step="10" :value="(theme.styles[selectedKey].opacity || 1) * 100"
                                       x-on:input.debounce.150ms="theme.styles[selectedKey].opacity = $event.target.value/100; queueChange()">
                                <span x-text="theme.styles[selectedKey].opacity !== undefined ? `${Math.round(theme.styles[selectedKey].opacity * 100)}%` : '100%'"></span>
                            </label>
                            <button type="button" class="studio-add w-full mt-4" x-on:click="$refs.photoInput.click()">Replace Image</button>
                        </section>

                        <!-- Element Section Styles -->
                        <section class="studio-control-section" x-show="selectedType === 'section'">
                            <p class="studio-kicker">Section styling</p>
                            <label>Background Color
                                <input type="color" x-model="theme.styles[selectedKey].background" x-on:input.debounce.150ms="queueChange()">
                            </label>
                            <label>Padding
                                <select x-model="theme.styles[selectedKey].padding" x-on:change="queueChange()">
                                    <option value="">Default</option>
                                    <option value="4px">Compact (4px)</option>
                                    <option value="10px">Medium (10px)</option>
                                    <option value="20px">Generous (20px)</option>
                                </select>
                            </label>
                            <label>Margin bottom
                                <select x-model="theme.styles[selectedKey].margin" x-on:change="queueChange()">
                                    <option value="">Default</option>
                                    <option value="0 0 4px">Compact (4px)</option>
                                    <option value="0 0 12px">Medium (12px)</option>
                                    <option value="0 0 24px">Generous (24px)</option>
                                </select>
                            </label>
                            <label>Border width
                                <select x-model="theme.styles[selectedKey].border_width" x-on:change="queueChange()">
                                    <option value="">None</option>
                                    <option value="1px">Thin (1px)</option>
                                    <option value="2px">Medium (2px)</option>
                                </select>
                            </label>
                            <label>Border Color
                                <input type="color" x-model="theme.styles[selectedKey].border_color" x-on:input.debounce.150ms="queueChange()">
                            </label>
                            <label>Shadow
                                <select x-model="theme.styles[selectedKey].shadow" x-on:change="queueChange()">
                                    <option value="">None</option>
                                    <option value="0 1px 3px rgba(0,0,0,0.1)">Soft</option>
                                    <option value="0 4px 6px rgba(0,0,0,0.15)">Medium</option>
                                </select>
                            </label>
                        </section>

                        <!-- Page / Global Styles -->
                        <div x-show="selectedType === 'none' || selectedType === 'page'" class="space-y-6">
                            <section><p class="studio-kicker">Theme presets</p><div class="mt-3 flex flex-wrap gap-3">@foreach ([['#3155e7','#142845'],['#7c3aed','#25164b'],['#07876b','#133b35'],['#111827','#1f2937'],['#e11d48','#4c1723'],['#ffffff','#f8fafc']] as [$accent,$secondary])<button type="button" class="studio-color-dot" style="--swatch:{{ $accent }}" x-on:click="applyPreset('{{ $accent }}','{{ $secondary }}')" :class="theme.accent_color === '{{ $accent }}' && 'is-active'" aria-label="Apply color preset"></button>@endforeach<label class="studio-color-dot add"><span>+</span><input type="color" x-model="theme.accent_color" x-on:input.debounce.150ms="queueChange()"></label></div></section>
                            <section class="studio-control-section"><p class="studio-kicker">Custom colors</p><label>Accent<input type="color" x-model="theme.accent_color" x-on:input.debounce.150ms="queueChange()"></label><label>Sidebar<input type="color" x-model="theme.secondary_color" x-on:input.debounce.150ms="queueChange()"></label></section>
                            <section class="studio-control-section"><p class="studio-kicker">Fonts & Headers</p><label>Headings font<select x-model="theme.heading_font" x-on:change="queueChange()">@foreach (['Poppins','Inter','Roboto','Lato','Merriweather'] as $font)<option>{{ $font }}</option>@endforeach</select></label><label>Body font<select x-model="currentFont" x-on:change="queueChange()">@foreach (['Inter','Roboto','Lato','Poppins','Merriweather'] as $font)<option>{{ $font }}</option>@endforeach</select></label><label>Font size<input type="range" min="80" max="125" step="5" x-model.number="currentFontScale" x-on:input.debounce.150ms="queueChange()"><span x-text="`${currentFontScale}%`"></span></label><label>Header color<input type="color" x-model="theme.header_color" x-on:input.debounce.150ms="queueChange()"></label><label>Header size<input type="range" min="70" max="150" step="5" x-model.number="theme.header_scale" x-on:input.debounce.150ms="queueChange()"><span x-text="`${theme.header_scale || 100}%`"></span></label></section>
                        </div>
                    </div>

                    <div x-show="designTab === 'page'" class="space-y-6">
                        <section class="studio-control-section"><p class="studio-kicker">Layout</p><label>Columns<select x-model="theme.layout" x-on:change="queueChange()"><option value="two-column">Two columns</option><option value="one-column">One column</option></select></label><label x-show="theme.layout === 'two-column'">Sidebar width<input type="range" min="28" max="42" x-model.number="theme.sidebar_width" x-on:input.debounce.150ms="queueChange()"><span x-text="`${theme.sidebar_width}%`"></span></label><label>Photo position<select x-model="theme.photo_position" x-on:change="queueChange()"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select></label><label>Spacing<select x-model="theme.density" x-on:change="queueChange()"><option value="compact">Compact</option><option value="balanced">Comfortable</option><option value="spacious">Spacious</option></select></label><label>Page size<select x-model="theme.page_size" x-on:change="queueChange()"><option value="a4">A4</option><option value="letter">Letter</option></select></label></section>
                        <section class="studio-control-section"><p class="studio-kicker">Page style</p><label>Background<input type="color" x-model="theme.page_background" x-on:input.debounce.150ms="queueChange()"></label><label class="toggle-row"><span>Section dividers</span><input type="checkbox" x-model="theme.dividers" x-on:change="queueChange()"></label><label class="toggle-row"><span>Page shadow</span><input type="checkbox" x-model="theme.shadow" x-on:change="queueChange()"></label></section>
                    </div>

                    <div x-show="designTab === 'settings'" class="space-y-5">
                        <section><div class="flex items-center justify-between"><p class="studio-kicker">Live ATS score</p><strong class="text-xl text-indigo-700" x-text="`${liveAtsScore}%`"></strong></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-600 transition-all" :style="`width:${liveAtsScore}%`"></div></div><p class="mt-2 text-xs text-slate-500" x-text="latestAtsScore === null ? 'Live readiness estimate based on the current resume.' : `Latest full ATS scan: ${latestAtsScore}%`"></p></section>
                        <section><div class="flex items-center justify-between"><p class="studio-kicker">Version history</p><span class="text-xs text-slate-400">{{ $resume?->versions?->count() ?? 0 }} saved</span></div>@if ($resume && $resume->versions->isNotEmpty())<div class="mt-3 space-y-2">@foreach ($resume->versions->take(6) as $version)<div class="rounded-lg border border-slate-200 p-3"><div class="flex items-center justify-between gap-2"><div><p class="text-xs font-semibold">Version {{ $version->version_number }}</p><p class="text-[10px] text-slate-500">{{ $version->created_at?->diffForHumans() }}</p></div><button type="submit" form="restore-version-{{ $version->id }}" class="text-xs font-semibold text-indigo-600">Restore</button></div></div>@endforeach</div>@else<p class="mt-2 text-xs text-slate-500">A version is created when you use Save.</p>@endif</section>
                        <section class="rounded-xl border border-slate-200 p-3"><p class="studio-kicker">Collaboration ready</p><p class="mt-2 text-xs leading-5 text-slate-500">Stable section identifiers and lock state are stored for future shared editing and comments.</p></section>
                        <button type="button" class="studio-reset" x-on:click="resetDesign()">Reset design to default</button>
                    </div>
                </div>
            </aside>

            <nav class="resume-studio-mobile-nav"><button type="button" :class="mobilePane === 'editor' && 'is-active'" x-on:click="mobilePane='editor'">Edit</button><button type="button" :class="mobilePane === 'canvas' && 'is-active'" x-on:click="mobilePane='canvas'">Canvas</button><button type="button" :class="mobilePane === 'design' && 'is-active'" x-on:click="mobilePane='design'">Design</button></nav>
        </form>

        @if ($resume)@foreach ($resume->versions->take(6) as $version)<form id="restore-version-{{ $version->id }}" method="POST" action="{{ route('resumes.versions.restore', [$resume,$version]) }}" class="hidden">@csrf</form>@endforeach@endif
    </div>
</x-builder-layout>

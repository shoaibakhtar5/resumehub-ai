@php
    $profile = $resume?->profile;
    $experience = $resume?->experiences?->first();
    $education = $resume?->educations?->first();
    $settings = $resume?->settings ?? [];
    $selectedTemplate = old('template_id', $selectedTemplate ?? $resume?->template_id);
    $templateOptions = ['' => 'No template'];

    foreach (($templates ?? collect()) as $template) {
        $templateOptions[$template->id] = $template->name;
    }
@endphp

<x-app-layout title="Resume Builder" mode="user">
    <div class="space-y-8">
        <x-ui.page-header eyebrow="Editing mode" title="{{ $resume ? 'Edit Resume' : 'Create Resume' }}" description="Edit structured resume content on the left and keep a live, export-ready preview in view on larger screens.">
            @if ($resume)
                <x-ui.button href="{{ route('resume.preview', $resume) }}" icon="eye">Preview</x-ui.button>
            @endif
            <x-ui.button href="{{ route('resume.templates') }}" variant="secondary" icon="squares-2x2">Templates</x-ui.button>
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.card class="border-success/30 bg-success/10 text-on-surface">{{ session('status') }}</x-ui.card>
        @endif

        <form method="POST" action="{{ $resume ? route('resumes.update', $resume) : route('resumes.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(420px,1fr)]">
            @csrf
            @if ($resume)
                @method('PATCH')
            @endif

            <section class="space-y-6">
                <x-ui.card>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Resume Title" id="title" name="title" type="text" value="{{ old('title', $resume?->title ?? 'Untitled Resume') }}" required :error="$errors->first('title')" />
                        <x-ui.select label="Template" id="template_id" name="template_id" :options="$templateOptions" :selected="$selectedTemplate" />
                        <x-ui.input label="Target Role" id="target_role" name="target_role" type="text" value="{{ old('target_role', $resume?->target_role) }}" :error="$errors->first('target_role')" />
                        <x-ui.input label="Target Company" id="target_company" name="target_company" type="text" value="{{ old('target_company', $resume?->target_company) }}" :error="$errors->first('target_company')" />
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Personal Details</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Full Name" id="profile_full_name" name="profile[full_name]" type="text" value="{{ old('profile.full_name', $profile?->full_name ?? auth()->user()->name) }}" />
                        <x-ui.input label="Headline" id="profile_headline" name="profile[headline]" type="text" value="{{ old('profile.headline', $profile?->headline ?? $resume?->target_role) }}" />
                        <x-ui.input label="Email" id="profile_email" name="profile[email]" type="email" value="{{ old('profile.email', $profile?->email ?? auth()->user()->email) }}" />
                        <x-ui.input label="Phone" id="profile_phone" name="profile[phone]" type="tel" value="{{ old('profile.phone', $profile?->phone ?? auth()->user()->phone) }}" />
                        <x-ui.input label="Website" id="profile_website" name="profile[website]" type="url" value="{{ old('profile.website', $profile?->website) }}" />
                        <x-ui.input label="Location" id="profile_location" name="profile[location]" type="text" value="{{ old('profile.location', $profile?->location) }}" />
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Profile Summary</h2>
                    <x-ui.textarea label="Summary" id="summary" name="summary" class="mt-5 min-h-36">{{ old('summary', $settings['summary'] ?? '') }}</x-ui.textarea>
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="font-display text-headline-md text-on-surface">Experience</h2>
                        <a href="{{ route('ai.studio') }}" class="font-display text-label-md text-primary rh-focus">Improve with AI</a>
                    </div>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Company" id="experience_company" name="experiences[0][company]" type="text" value="{{ old('experiences.0.company', $experience?->company) }}" />
                        <x-ui.input label="Position" id="experience_position" name="experiences[0][position]" type="text" value="{{ old('experiences.0.position', $experience?->position) }}" />
                        <x-ui.input label="Location" id="experience_location" name="experiences[0][location]" type="text" value="{{ old('experiences.0.location', $experience?->location) }}" />
                        <x-ui.input label="Start Date" id="experience_start_date" name="experiences[0][start_date]" type="date" value="{{ old('experiences.0.start_date', optional($experience?->start_date)->format('Y-m-d')) }}" />
                        <x-ui.input label="End Date" id="experience_end_date" name="experiences[0][end_date]" type="date" value="{{ old('experiences.0.end_date', optional($experience?->end_date)->format('Y-m-d')) }}" />
                        <label class="mt-8 inline-flex items-center gap-2 text-body-sm text-on-surface-variant">
                            <input type="checkbox" name="experiences[0][is_current]" value="1" class="rounded border-border-light text-primary focus:ring-primary" @checked(old('experiences.0.is_current', $experience?->is_current))>
                            Current role
                        </label>
                    </div>
                    <x-ui.textarea label="Impact bullets" id="experience_description" name="experiences[0][description]" class="mt-5 min-h-36">{{ old('experiences.0.description', $experience?->description) }}</x-ui.textarea>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Education</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Institution" id="education_institution" name="educations[0][institution]" type="text" value="{{ old('educations.0.institution', $education?->institution) }}" />
                        <x-ui.input label="Degree" id="education_degree" name="educations[0][degree]" type="text" value="{{ old('educations.0.degree', $education?->degree) }}" />
                        <x-ui.input label="Field of Study" id="education_field" name="educations[0][field_of_study]" type="text" value="{{ old('educations.0.field_of_study', $education?->field_of_study) }}" />
                        <x-ui.input label="End Date" id="education_end_date" name="educations[0][end_date]" type="date" value="{{ old('educations.0.end_date', optional($education?->end_date)->format('Y-m-d')) }}" />
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="font-display text-headline-md text-on-surface">Skills Palette</h2>
                    <x-ui.textarea label="Skills" id="skills" name="skills" class="mt-5 min-h-28">{{ old('skills', implode(', ', $settings['skills'] ?? [])) }}</x-ui.textarea>
                    <p class="mt-2 text-body-sm text-on-surface-variant">Separate skills with commas or new lines.</p>
                </x-ui.card>

                <div class="flex flex-wrap gap-3">
                    <x-ui.button type="submit" icon="check">{{ $resume ? 'Save Resume' : 'Create Resume' }}</x-ui.button>
                    @if ($resume)
                        <x-ui.button href="{{ route('resume.preview', $resume) }}" variant="secondary" icon="eye">Preview</x-ui.button>
                    @endif
                </div>
            </section>

            <aside class="xl:sticky xl:top-24 xl:self-start">
                <div class="rh-panel p-4">
                    <div class="rounded-lg bg-white p-8 shadow-soft">
                        <div class="border-b border-border-light pb-6">
                            <h2 class="font-display text-4xl font-bold text-on-surface">{{ old('profile.full_name', $profile?->full_name ?? auth()->user()->name) }}</h2>
                            <p class="mt-2 text-primary">{{ old('profile.headline', $profile?->headline ?? $resume?->target_role ?? 'Target role') }}</p>
                            <p class="mt-4 text-body-sm text-on-surface-variant">{{ old('profile.email', $profile?->email ?? auth()->user()->email) }} | {{ old('profile.phone', $profile?->phone ?? auth()->user()->phone ?? 'Phone') }}</p>
                        </div>
                        <section class="mt-6">
                            <h3 class="font-display text-label-md uppercase text-primary">Profile</h3>
                            <p class="mt-3 text-body-sm leading-6 text-on-surface-variant">{{ old('summary', $settings['summary'] ?? 'Add a targeted professional summary to sharpen this resume.') }}</p>
                        </section>
                        <section class="mt-6">
                            <h3 class="font-display text-label-md uppercase text-primary">Experience</h3>
                            <p class="mt-3 font-display text-label-md text-on-surface">{{ old('experiences.0.position', $experience?->position ?? 'Role') }} at {{ old('experiences.0.company', $experience?->company ?? 'Company') }}</p>
                            <p class="mt-2 text-body-sm text-on-surface-variant">{{ old('experiences.0.description', $experience?->description ?? 'Add outcome-focused bullets here.') }}</p>
                        </section>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</x-app-layout>

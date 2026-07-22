<?php

namespace App\Http\Requests\Resume;

use App\Models\Resume;
use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResumeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Resume::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];
        $profile = $this->input('profile');
        $socialLinks = $this->input('social_links');
        $projects = $this->input('projects');

        foreach (['social_links', 'experiences', 'educations', 'projects', 'skills', 'languages', 'certifications', 'awards', 'references', 'sections'] as $collection) {
            $items = $this->input($collection);
            if (! is_array($items)) {
                continue;
            }
            $normalized[$collection] = array_map(function ($item): mixed {
                if (! is_array($item)) {
                    return $item;
                }
                foreach (['is_visible', 'is_current', 'available_on_request'] as $key) {
                    if (array_key_exists($key, $item)) {
                        $item[$key] = filter_var($item[$key], FILTER_VALIDATE_BOOL);
                    }
                }
                return $item;
            }, $items);
        }

        if (is_array($profile)) {
            $profile['website'] = $this->normalizeUrl($profile['website'] ?? null);
            $normalized['profile'] = $profile;
        }

        if (is_array($socialLinks)) {
            $socialLinks = array_map(function ($link): mixed {
                if (is_array($link)) {
                    $link['url'] = $this->normalizeUrl($link['url'] ?? null);
                    if (array_key_exists('is_visible', $link)) {
                        $link['is_visible'] = filter_var($link['is_visible'], FILTER_VALIDATE_BOOL);
                    }
                }

                return $link;
            }, $socialLinks);
            $normalized['social_links'] = $socialLinks;
        }

        if (is_array($projects)) {
            $projects = array_map(function ($project): mixed {
                if (is_array($project)) {
                    $project['url'] = $this->normalizeUrl($project['url'] ?? null);
                    $project['repository_url'] = $this->normalizeUrl($project['repository_url'] ?? null);
                    foreach (['is_visible', 'is_current'] as $key) {
                        if (array_key_exists($key, $project)) {
                            $project[$key] = filter_var($project[$key], FILTER_VALIDATE_BOOL);
                        }
                    }
                }

                return $project;
            }, $projects);
            $normalized['projects'] = $projects;
        }

        $this->merge($normalized);
    }

    private function normalizeUrl(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value === '' || preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        return 'https://'.$value;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'target_company' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
            'template_id' => ['nullable', Rule::exists(Template::class, 'id')],
            'summary' => ['nullable', 'string', 'max:3000'],
            'present_collections' => ['nullable', 'array'],
            'present_collections.*' => ['string', Rule::in([
                'social_links', 'experiences', 'educations', 'projects', 'skills', 'languages',
                'certifications', 'awards', 'references', 'custom_sections', 'sections',
            ])],
            'skills' => ['nullable'],
            'skills.*.name' => ['nullable', 'string', 'max:120'],
            'skills.*.category' => ['nullable', 'string', 'max:120'],
            'skills.*.proficiency' => ['nullable', 'string', 'max:60'],
            'skills.*.years_experience' => ['nullable', 'numeric', 'min:0', 'max:80'],
            'skills.*.is_visible' => ['nullable', 'boolean'],
            'skills.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'theme' => ['nullable', 'array'],
            'theme.accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.font_pairing' => ['nullable', Rule::in(['modern', 'classic', 'executive', 'technical'])],
            'theme.heading_font' => ['nullable', Rule::in(['Inter', 'Roboto', 'Lato', 'Poppins', 'Merriweather'])],
            'theme.body_font' => ['nullable', Rule::in(['Inter', 'Roboto', 'Lato', 'Poppins', 'Merriweather'])],
            'theme.font_scale' => ['nullable', 'integer', 'min:80', 'max:125'],
            'theme.density' => ['nullable', Rule::in(['compact', 'balanced', 'spacious'])],
            'theme.page_size' => ['nullable', Rule::in(['letter', 'a4'])],
            'theme.layout' => ['nullable', Rule::in(['one-column', 'two-column'])],
            'theme.sidebar_width' => ['nullable', 'integer', 'min:28', 'max:42'],
            'theme.photo_position' => ['nullable', Rule::in(['left', 'center', 'right'])],
            'theme.section_spacing' => ['nullable', Rule::in(['small', 'medium', 'large'])],
            'theme.content_width' => ['nullable', Rule::in(['compact', 'standard', 'wide'])],
            'theme.page_background' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.dividers' => ['nullable', 'boolean'],
            'theme.shadow' => ['nullable', 'boolean'],
            'theme.header_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.header_scale' => ['nullable', 'integer', 'min:70', 'max:150'],
            'theme.styles' => ['nullable', 'array'],
            'theme.styles.*' => ['nullable', 'array'],
            'profile.full_name' => ['nullable', 'string', 'max:255'],
            'profile.headline' => ['nullable', 'string', 'max:255'],
            'profile.email' => ['nullable', 'email', 'max:255'],
            'profile.phone' => ['nullable', 'string', 'max:40'],
            'profile.website' => ['nullable', 'url', 'max:255'],
            'profile.location' => ['nullable', 'string', 'max:255'],
            'profile.city' => ['nullable', 'string', 'max:255'],
            'profile.state' => ['nullable', 'string', 'max:255'],
            'profile.country' => ['nullable', 'string', 'max:255'],
            'profile.postal_code' => ['nullable', 'string', 'max:40'],
            'profile.photo_path' => ['nullable', 'string', 'max:2048'],
            'profile.metadata' => ['nullable', 'array'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.id' => ['nullable', 'integer'],
            'social_links.*.platform' => ['nullable', 'string', 'max:80'],
            'social_links.*.label' => ['nullable', 'string', 'max:255'],
            'social_links.*.url' => ['nullable', 'url', 'max:2048'],
            'social_links.*.is_visible' => ['nullable', 'boolean'],
            'social_links.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'sections' => ['nullable', 'array'],
            'sections.*.section_key' => ['required_with:sections', 'string', 'max:100'],
            'sections.*.title' => ['nullable', 'string', 'max:255'],
            'sections.*.is_visible' => ['nullable', 'boolean'],
            'sections.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'sections.*.settings' => ['nullable', 'array'],
            'experiences' => ['nullable', 'array'],
            'experiences.*.id' => ['nullable', 'integer'],
            'experiences.*.company' => ['nullable', 'string', 'max:255'],
            'experiences.*.position' => ['nullable', 'string', 'max:255'],
            'experiences.*.employment_type' => ['nullable', 'string', 'max:80'],
            'experiences.*.location' => ['nullable', 'string', 'max:255'],
            'experiences.*.start_date' => ['nullable', 'date'],
            'experiences.*.end_date' => ['nullable', 'date'],
            'experiences.*.is_current' => ['nullable', 'boolean'],
            'experiences.*.description' => ['nullable', 'string', 'max:5000'],
            'experiences.*.technologies' => ['nullable'],
            'experiences.*.is_visible' => ['nullable', 'boolean'],
            'experiences.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'educations' => ['nullable', 'array'],
            'educations.*.id' => ['nullable', 'integer'],
            'educations.*.institution' => ['nullable', 'string', 'max:255'],
            'educations.*.degree' => ['nullable', 'string', 'max:255'],
            'educations.*.field_of_study' => ['nullable', 'string', 'max:255'],
            'educations.*.location' => ['nullable', 'string', 'max:255'],
            'educations.*.start_date' => ['nullable', 'date'],
            'educations.*.end_date' => ['nullable', 'date'],
            'educations.*.is_current' => ['nullable', 'boolean'],
            'educations.*.grade' => ['nullable', 'string', 'max:255'],
            'educations.*.description' => ['nullable', 'string', 'max:3000'],
            'educations.*.is_visible' => ['nullable', 'boolean'],
            'educations.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'projects' => ['nullable', 'array'],
            'projects.*.id' => ['nullable', 'integer'],
            'projects.*.name' => ['nullable', 'string', 'max:255'],
            'projects.*.role' => ['nullable', 'string', 'max:255'],
            'projects.*.url' => ['nullable', 'url', 'max:2048'],
            'projects.*.repository_url' => ['nullable', 'url', 'max:2048'],
            'projects.*.start_date' => ['nullable', 'date'],
            'projects.*.end_date' => ['nullable', 'date'],
            'projects.*.is_current' => ['nullable', 'boolean'],
            'projects.*.description' => ['nullable', 'string', 'max:5000'],
            'projects.*.technologies' => ['nullable'],
            'projects.*.is_visible' => ['nullable', 'boolean'],
            'projects.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'languages' => ['nullable'],
            'languages.*.name' => ['nullable', 'string', 'max:120'],
            'languages.*.iso_code' => ['nullable', 'string', 'max:20'],
            'languages.*.proficiency' => ['nullable', 'string', 'max:80'],
            'languages.*.is_visible' => ['nullable', 'boolean'],
            'languages.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'certifications' => ['nullable', 'array'],
            'certifications.*.id' => ['nullable', 'integer'],
            'certifications.*.name' => ['nullable', 'string', 'max:255'],
            'certifications.*.issuer' => ['nullable', 'string', 'max:255'],
            'certifications.*.issued_at' => ['nullable', 'date'],
            'certifications.*.expires_at' => ['nullable', 'date'],
            'certifications.*.credential_id' => ['nullable', 'string', 'max:255'],
            'certifications.*.credential_url' => ['nullable', 'url', 'max:2048'],
            'certifications.*.description' => ['nullable', 'string', 'max:3000'],
            'certifications.*.is_visible' => ['nullable', 'boolean'],
            'certifications.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'awards' => ['nullable', 'array'],
            'awards.*.id' => ['nullable', 'integer'],
            'awards.*.title' => ['nullable', 'string', 'max:255'],
            'awards.*.issuer' => ['nullable', 'string', 'max:255'],
            'awards.*.awarded_at' => ['nullable', 'date'],
            'awards.*.description' => ['nullable', 'string', 'max:3000'],
            'awards.*.is_visible' => ['nullable', 'boolean'],
            'awards.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'references' => ['nullable', 'array'],
            'references.*.id' => ['nullable', 'integer'],
            'references.*.name' => ['nullable', 'string', 'max:255'],
            'references.*.title' => ['nullable', 'string', 'max:255'],
            'references.*.company' => ['nullable', 'string', 'max:255'],
            'references.*.email' => ['nullable', 'email', 'max:255'],
            'references.*.phone' => ['nullable', 'string', 'max:40'],
            'references.*.relationship' => ['nullable', 'string', 'max:255'],
            'references.*.available_on_request' => ['nullable', 'boolean'],
            'references.*.is_visible' => ['nullable', 'boolean'],
            'references.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'custom_sections' => ['nullable', 'array'],
            'custom_sections.*.title' => ['nullable', 'string', 'max:255'],
            'custom_sections.*.description' => ['nullable', 'string', 'max:3000'],
            'custom_sections.*.is_visible' => ['nullable', 'boolean'],
            'custom_sections.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'custom_sections.*.settings' => ['nullable', 'array'],
            'custom_sections.*.items' => ['nullable', 'array'],
            'custom_sections.*.items.*.title' => ['nullable', 'string', 'max:255'],
            'custom_sections.*.items.*.subtitle' => ['nullable', 'string', 'max:255'],
            'custom_sections.*.items.*.url' => ['nullable', 'url', 'max:2048'],
            'custom_sections.*.items.*.start_date' => ['nullable', 'date'],
            'custom_sections.*.items.*.end_date' => ['nullable', 'date'],
            'custom_sections.*.items.*.description' => ['nullable', 'string', 'max:5000'],
            'custom_sections.*.items.*.fields' => ['nullable', 'array'],
            'custom_sections.*.items.*.is_visible' => ['nullable', 'boolean'],
            'custom_sections.*.items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

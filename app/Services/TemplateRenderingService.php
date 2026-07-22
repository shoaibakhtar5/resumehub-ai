<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\Template;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class TemplateRenderingService
{
    private const PLACEHOLDERS = [
        'full_name', 'job_title', 'email', 'phone', 'website', 'location', 'photo',
        'summary', 'experiences', 'education', 'skills', 'projects', 'certifications',
        'languages', 'awards', 'references', 'social_links', 'theme_color',
        'content_sections', 'sidebar_sections', 'layout_class', 'profile_class',
    ];

    public function allowedPlaceholders(): array
    {
        return self::PLACEHOLDERS;
    }

    public function placeholderLabels(): array
    {
        return collect(self::PLACEHOLDERS)->mapWithKeys(fn (string $key) => [$key => Str::headline($key)])->all();
    }

    public function render(?Template $template, Resume|array|null $source, bool $forPdf = false, bool $embedded = false): string
    {
        $payload = $source instanceof Resume ? $this->payloadFromResume($source) : ($source ?? []);
        $values = $this->values($payload, $template, $forPdf);
        $html = $this->source($template);

        $html = preg_replace('/\bsrc\s*=\s*(["\'])\s*{{\s*photo\s*}}\s*\1/i', 'src="'.$values['_photo_src'].'"', $html) ?? $html;

        foreach (self::PLACEHOLDERS as $placeholder) {
            $html = preg_replace('/{{\s*'.preg_quote($placeholder, '/').'\s*}}/i', $values[$placeholder] ?? '', $html) ?? $html;
        }

        $html = $this->injectTheme($html, $payload);
        
        // Inject Google Fonts link globally for exact font fidelity across preview, print, and PDF
        $googleFontsLink = '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Lato:ital,wght@0,300;0,400;0,700;1,400&family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Poppins:wght@300;400;500;600;700;800&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap">';
        $html = preg_replace('/<\/head>/i', $googleFontsLink.'</head>', $html, 1) ?? $html;

        $fontFamily = data_get($template?->config, 'font_family');
        if (is_string($fontFamily) && preg_match('/^[A-Za-z0-9 ,\-\'\"]+$/', $fontFamily)) {
            $html = preg_replace('/<\/head>/i', '<style>body{font-family:'.$fontFamily.'}</style></head>', $html, 1) ?? $html;
        }

        if ($forPdf) {
            $pdfCssReset = '<style id="rh-pdf-reset">
                @page { size: A4 portrait; margin: 0; }
                @media print, screen {
                    html, body { width: 100% !important; height: 100% !important; margin: 0 !important; padding: 0 !important; background: #ffffff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                    .resume-wrapper, .rh-page, .rh-embedded-template { width: 100% !important; min-height: 100vh !important; max-width: none !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
                }
            </style>';
            $html = preg_replace('/<\/head>/i', $pdfCssReset.'</head>', $html, 1) ?? $html;
        }

        return $embedded ? $this->embeddedDocument($html) : $html;
    }

    public function demoPayload(): array
    {
        return [
            'title' => 'Software Engineer Resume', 'target_role' => 'Senior Software Engineer',
            'profile' => ['full_name' => 'Alex Morgan', 'headline' => 'Senior Software Engineer', 'email' => 'alex.morgan@example.com', 'phone' => '+1 555 014 2288', 'website' => 'alexmorgan.dev', 'location' => 'San Francisco, CA'],
            'summary' => 'Product-focused engineer with eight years of experience building reliable web applications and leading collaborative teams.',
            'experiences' => [['position' => 'Senior Software Engineer', 'company' => 'Northstar Labs', 'location' => 'San Francisco, CA', 'start_date' => '2022-01-01', 'is_current' => true, 'description' => 'Led delivery of customer-facing platforms and improved application performance across critical workflows.']],
            'educations' => [['degree' => 'BSc Computer Science', 'institution' => 'Pacific University', 'start_date' => '2013-09-01', 'end_date' => '2017-06-01']],
            'skills' => [['name' => 'Laravel'], ['name' => 'PHP'], ['name' => 'JavaScript'], ['name' => 'System Design']],
            'projects' => [['name' => 'Workflow Platform', 'role' => 'Technical Lead', 'description' => 'Designed a secure workflow product used by distributed operations teams.']],
            'languages' => [['name' => 'English', 'proficiency' => 'Native'], ['name' => 'Spanish', 'proficiency' => 'Professional']],
            'certifications' => [['name' => 'AWS Certified Developer', 'issuer' => 'Amazon Web Services', 'issued_at' => '2024-01-01']],
            'awards' => [['title' => 'Engineering Excellence Award', 'issuer' => 'Northstar Labs', 'awarded_at' => '2024-05-01']],
            'references' => [['name' => 'Jordan Lee', 'title' => 'VP Engineering', 'company' => 'Northstar Labs']],
            'theme' => ['accent_color' => '#4f46e5'],
        ];
    }

    private function source(?Template $template): string
    {
        if ($template?->package_path && Storage::disk('local')->exists($template->package_path)) {
            return Storage::disk('local')->get($template->package_path);
        }

        return file_get_contents(resource_path('templates/resumes/default.html')) ?: '';
    }

    private function embeddedDocument(string $html): string
    {
        preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $html, $styles);
        preg_match('/<body\b([^>]*)>(.*?)<\/body>/is', $html, $body);
        preg_match('/\bclass\s*=\s*(["\'])(.*?)\1/is', $body[1] ?? '', $bodyClass);
        $classes = collect(preg_split('/\s+/', trim($bodyClass[2] ?? '')) ?: [])
            ->filter(fn (string $class): bool => preg_match('/^[A-Za-z0-9_-]+$/', $class) === 1)
            ->implode(' ');
        $content = $body[2] ?? $html;

        return implode('', array_map(fn (string $css) => '<style>'.$css.'</style>', $styles[1] ?? []))
            .'<div class="rh-embedded-template'.($classes ? ' '.$classes : '').'">'.$content.'</div>';
    }

    private function payloadFromResume(Resume $resume): array
    {
        $resume->loadMissing(['profile', 'summary', 'socialLinks', 'experiences', 'educations', 'skills', 'projects', 'languages', 'certifications', 'awards', 'references', 'sections', 'template']);

        return [
            'title' => $resume->title, 'target_role' => $resume->target_role,
            'profile' => $resume->profile?->toArray() ?? [],
            'summary' => $resume->summary?->content ?? data_get($resume->settings, 'summary', ''),
            'social_links' => $resume->socialLinks->toArray(), 'experiences' => $resume->experiences->toArray(),
            'educations' => $resume->educations->toArray(),
            'skills' => $resume->skills->map(fn ($item) => ['name' => $item->name, 'proficiency' => $item->pivot?->proficiency, 'is_visible' => $item->pivot?->is_visible ?? true])->all(),
            'projects' => $resume->projects->toArray(),
            'languages' => $resume->languages->map(fn ($item) => ['name' => $item->name, 'proficiency' => $item->pivot?->proficiency, 'is_visible' => $item->pivot?->is_visible ?? true])->all(),
            'certifications' => $resume->certifications->toArray(), 'awards' => $resume->awards->toArray(),
            'references' => $resume->references->toArray(), 'sections' => $resume->sections->toArray(),
            'theme' => data_get($resume->settings, 'theme', []),
        ];
    }

    private function wrapSelectable(string $value, string $key, bool $forPdf): string
    {
        if ($forPdf || trim($value) === '') {
            return $value;
        }
        return '<span data-rh-selectable="'.e($key).'">'.$value.'</span>';
    }

    private function values(array $payload, ?Template $template, bool $forPdf): array
    {
        $profile = is_array($payload['profile'] ?? null) ? $payload['profile'] : [];
        $summary = $payload['summary'] ?? '';
        if (is_array($summary)) $summary = $summary['content'] ?? '';
        $accent = data_get($payload, 'theme.accent_color') ?: data_get($template?->config, 'primary_color', '#4f46e5');
        $accent = is_string($accent) && preg_match('/^#[0-9a-f]{6}$/i', $accent) ? $accent : '#4f46e5';

        $sections = $this->sections($payload);
        $visible = fn (string $key): bool => (bool) data_get($sections->firstWhere('section_key', $key), 'is_visible', true);
        $blocks = [
            'summary' => $visible('summary') ? $this->section('Professional Summary', $this->paragraph($summary), 'summary', $forPdf) : '',
            'experience' => $visible('experience') ? $this->section('Experience', $this->items($payload['experiences'] ?? [], 'experience', $forPdf), 'experience', $forPdf) : '',
            'education' => $visible('education') ? $this->section('Education', $this->items($payload['educations'] ?? $payload['education'] ?? [], 'education', $forPdf), 'education', $forPdf) : '',
            'skills' => $visible('skills') ? $this->section('Skills', $this->tags($payload['skills'] ?? [], 'skills', false, $forPdf), 'skills', $forPdf) : '',
            'projects' => $visible('projects') ? $this->section('Projects', $this->items($payload['projects'] ?? [], 'project', $forPdf), 'projects', $forPdf) : '',
            'certifications' => $visible('certifications') ? $this->section('Certifications', $this->items($payload['certifications'] ?? [], 'certification', $forPdf), 'certifications', $forPdf) : '',
            'languages' => $visible('languages') ? $this->section('Languages', $this->tags($payload['languages'] ?? [], 'languages', true, $forPdf), 'languages', $forPdf) : '',
            'awards' => $visible('awards') ? $this->section('Awards', $this->items($payload['awards'] ?? [], 'award', $forPdf), 'awards', $forPdf) : '',
            'references' => $visible('references') ? $this->section('References', $this->items($payload['references'] ?? [], 'reference', $forPdf), 'references', $forPdf) : '',
            'social_links' => $this->section('Links', $this->tags($payload['social_links'] ?? [], 'social_links', true, $forPdf), 'social-links', $forPdf),
        ];
        $layout = data_get($payload, 'theme.layout', 'two-column');
        $sidebarKeys = $layout === 'one-column' ? [] : ['skills', 'languages', 'social_links'];
        $orderedKeys = array_values(array_unique(array_merge($sections->pluck('section_key')->all(), array_keys($blocks))));

        return [
            'full_name' => $this->wrapSelectable(e($profile['full_name'] ?? $payload['title'] ?? ''), 'profile.full_name', $forPdf),
            'job_title' => $this->wrapSelectable(e($profile['headline'] ?? $payload['target_role'] ?? ''), 'profile.headline', $forPdf),
            'email' => $this->wrapSelectable(e($profile['email'] ?? ''), 'profile.email', $forPdf),
            'phone' => $this->wrapSelectable(e($profile['phone'] ?? ''), 'profile.phone', $forPdf),
            'website' => $this->wrapSelectable(e($profile['website'] ?? ''), 'profile.website', $forPdf),
            'location' => $this->wrapSelectable(e($profile['location'] ?? ''), 'profile.location', $forPdf),
            'photo' => $this->photo($profile['photo_path'] ?? null, $forPdf),
            '_photo_src' => e($this->photoSource($profile['photo_path'] ?? null, $forPdf)),
            'summary' => $blocks['summary'], 'experiences' => $blocks['experience'], 'education' => $blocks['education'],
            'skills' => $blocks['skills'], 'projects' => $blocks['projects'], 'certifications' => $blocks['certifications'],
            'languages' => $blocks['languages'], 'awards' => $blocks['awards'], 'references' => $blocks['references'],
            'social_links' => $blocks['social_links'], 'theme_color' => $accent,
            'content_sections' => collect($orderedKeys)->reject(fn ($key) => in_array($key, $sidebarKeys, true))->map(fn ($key) => $blocks[$key] ?? '')->implode(''),
            'sidebar_sections' => collect($sidebarKeys)->map(fn ($key) => $blocks[$key] ?? '')->implode(''),
            'layout_class' => $layout === 'one-column' ? 'rh-layout-one-column' : 'rh-layout-two-column',
            'profile_class' => $visible('personal') ? '' : 'rh-hide-profile',
        ];
    }

    private function sections(array $payload): Collection
    {
        return collect($payload['sections'] ?? [])->filter(fn ($section) => is_array($section) && filled($section['section_key'] ?? null))->sortBy(fn ($section) => (int) ($section['sort_order'] ?? 0))->values();
    }

    private function items(array $items, string $type, bool $forPdf): string
    {
        return collect($items)->filter(fn ($item) => is_array($item) && ($item['is_visible'] ?? true))->values()->map(function (array $item, int $index) use ($type, $forPdf): string {
            [$title, $subtitle, $description, $date] = match ($type) {
                'experience' => [$item['position'] ?? '', $item['company'] ?? '', $item['description'] ?? '', $this->dateRange($item)],
                'education' => [$item['degree'] ?? $item['field_of_study'] ?? '', $item['institution'] ?? '', $item['description'] ?? '', $this->dateRange($item)],
                'project' => [$item['name'] ?? '', $item['role'] ?? '', $item['description'] ?? '', $this->dateRange($item)],
                'certification' => [$item['name'] ?? '', $item['issuer'] ?? '', $item['description'] ?? '', $this->date($item['issued_at'] ?? null)],
                'award' => [$item['title'] ?? '', $item['issuer'] ?? '', $item['description'] ?? '', $this->date($item['awarded_at'] ?? null)],
                default => [$item['name'] ?? '', trim(($item['title'] ?? '').' '.($item['company'] ?? '')), '', ''],
            };
            if (!filled($title) && !filled($subtitle) && !filled($description)) return '';
            $meta = collect([$subtitle, $item['location'] ?? null])->filter()->map(fn ($value) => e($value))->implode(' · ');
            $selectable = $forPdf ? '' : ' data-rh-selectable="item.'.$type.'.'.$index.'"';
            return '<article class="rh-item"'.$selectable.'><div class="rh-item-head"><div><strong>'.e($title).'</strong>'.($meta ? '<div class="rh-meta">'.$meta.'</div>' : '').'</div>'.($date ? '<time>'.e($date).'</time>' : '').'</div>'.($description ? '<p>'.nl2br(e($description)).'</p>' : '').'</article>';
        })->filter()->implode('');
    }

    private function tags(array $items, string $type, bool $withValue, bool $forPdf): string
    {
        return collect($items)->filter(fn ($item) => is_array($item) && ($item['is_visible'] ?? true) && filled($item['name'] ?? null))->values()->map(function (array $item, int $index) use ($type, $withValue, $forPdf): string {
            $value = $withValue ? ($item['proficiency'] ?? $item['url'] ?? '') : '';
            $selectable = $forPdf ? '' : ' data-rh-selectable="tag.'.$type.'.'.$index.'"';
            return '<span class="rh-tag"'.$selectable.'>'.e($item['name']).($value ? '<small>'.e($value).'</small>' : '').'</span>';
        })->implode('');
    }

    private function paragraph(mixed $value): string
    {
        return filled($value) ? '<p>'.nl2br(e((string) $value)).'</p>' : '';
    }

    private function section(string $title, string $content, string $key, bool $forPdf = false): string
    {
        if (trim($content) === '') return '';
        $selectable = $forPdf ? '' : ' data-rh-selectable="section.'.$key.'"';
        return '<section class="rh-section"'.$selectable.' data-resume-section="'.e($key).'"><h2>'.e($title).'</h2>'.$content.'</section>';
    }

    private function photo(mixed $path, bool $forPdf): string
    {
        $src = $this->photoSource($path, $forPdf);
        if ($src === '') return '';
        $selectable = $forPdf ? '' : ' data-rh-selectable="profile.photo"';
        return '<img class="rh-photo"'.$selectable.' src="'.e($src).'" alt="Profile photo">';
    }

    private function photoSource(mixed $path, bool $forPdf): string
    {
        if (!is_string($path) || trim($path) === '') return '';

        return $forPdf && str_starts_with($path, '/storage/') ? public_path(ltrim($path, '/')) : $path;
    }

    private function dateRange(array $item): string
    {
        $start = $this->date($item['start_date'] ?? null);
        $end = filter_var($item['is_current'] ?? false, FILTER_VALIDATE_BOOL) ? 'Present' : $this->date($item['end_date'] ?? null);
        return $start && $end ? $start.' – '.$end : ($start ?: $end);
    }

    private function date(mixed $value): string
    {
        if (!filled($value)) return '';
        try {
            return ($value instanceof DateTimeInterface ? CarbonImmutable::instance($value) : CarbonImmutable::parse($value))->format('M Y');
        } catch (Throwable) {
            return '';
        }
    }

    private function injectTheme(string $html, array $payload): string
    {
        $theme = is_array($payload['theme'] ?? null) ? $payload['theme'] : [];
        $color = fn (mixed $value, string $fallback): string => is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value) ? $value : $fallback;
        $font = fn (mixed $value, string $fallback): string => in_array($value, ['Inter', 'Roboto', 'Lato', 'Poppins', 'Merriweather'], true) ? $value : $fallback;
        $density = in_array($theme['density'] ?? null, ['compact', 'balanced', 'spacious'], true) ? $theme['density'] : 'balanced';
        $position = in_array($theme['photo_position'] ?? null, ['left', 'center', 'right'], true) ? $theme['photo_position'] : 'center';
        $margins = ['left' => '0 auto 22px 0', 'center' => '0 auto 22px', 'right' => '0 0 22px auto'];
        $gaps = ['compact' => '14px', 'balanced' => '20px', 'spacious' => '28px'];
        
        $css = '<style>:root{'
            .'--rh-accent:'.$color($theme['accent_color'] ?? null, '#3155e7').';'
            .'--rh-secondary:'.$color($theme['secondary_color'] ?? null, '#142845').';'
            .'--rh-page-bg:'.$color($theme['page_background'] ?? null, '#ffffff').';'
            .'--rh-heading-font:"'.$font($theme['heading_font'] ?? null, 'Poppins').'",sans-serif;'
            .'--rh-body-font:"'.$font($theme['body_font'] ?? null, 'Inter').'",sans-serif;'
            .'--rh-font-scale:'.(max(80, min(125, (int) ($theme['font_scale'] ?? 100))) / 100).';'
            .'--rh-sidebar-width:'.max(28, min(42, (int) ($theme['sidebar_width'] ?? 34))).'%;'
            .'--rh-section-gap:'.$gaps[$density].';'
            .'--rh-divider-display:'.(filter_var($theme['dividers'] ?? true, FILTER_VALIDATE_BOOL) ? 'block' : 'none').';'
            .'--rh-photo-margin:'.$margins[$position].';'
            .'--rh-header-color:'.$color($theme['header_color'] ?? null, '#1a252f').';'
            .'--rh-header-scale:'.(max(50, min(200, (int) ($theme['header_scale'] ?? 100))) / 100).';}';

        // Add section-specific styles
        foreach ($payload['sections'] ?? [] as $section) {
            $key = $section['section_key'] ?? null;
            if (!$key) continue;

            $settings = $section['settings'] ?? [];
            if (is_string($settings)) {
                $settings = json_decode($settings, true) ?: [];
            }

            $sFont = $settings['font_family'] ?? null;
            $sFontScale = $settings['font_scale'] ?? null;

            if ($sFont || $sFontScale) {
                $css .= '[data-resume-section="' . e($key) . '"] {';
                if ($sFont) {
                    $css .= 'font-family: "' . $font($sFont, 'Inter') . '", sans-serif !important;';
                }
                if ($sFontScale) {
                    $css .= 'font-size: ' . (int) $sFontScale . '% !important;';
                }
                $css .= '}';
            }
        }

        // Generate and inject dynamic custom element overrides
        $styles = $theme['styles'] ?? [];
        $customCss = '';
        foreach ($styles as $key => $rules) {
            if (empty($rules)) continue;
            $ruleStr = '';
            if (!empty($rules['font_family'])) $ruleStr .= 'font-family:"'.$rules['font_family'].'",sans-serif !important;';
            if (!empty($rules['font_size'])) $ruleStr .= 'font-size:'.$rules['font_size'].' !important;';
            if (!empty($rules['font_weight'])) $ruleStr .= 'font-weight:'.$rules['font_weight'].' !important;';
            if (!empty($rules['color'])) $ruleStr .= 'color:'.$rules['color'].' !important;';
            if (!empty($rules['text_align'])) $ruleStr .= 'text-align:'.$rules['text_align'].' !important;';
            if (!empty($rules['letter_spacing'])) $ruleStr .= 'letter-spacing:'.$rules['letter_spacing'].' !important;';
            if (!empty($rules['line_height'])) $ruleStr .= 'line-height:'.$rules['line_height'].' !important;';
            if (!empty($rules['italic'])) $ruleStr .= 'font-style:italic !important;';
            if (!empty($rules['underline'])) $ruleStr .= 'text-decoration:underline !important;';
            
            if (!empty($rules['border_radius'])) $ruleStr .= 'border-radius:'.$rules['border_radius'].' !important;';
            if (isset($rules['opacity'])) $ruleStr .= 'opacity:'.$rules['opacity'].' !important;';
            if (!empty($rules['width'])) $ruleStr .= 'width:'.$rules['width'].' !important;';
            if (!empty($rules['height'])) $ruleStr .= 'height:'.$rules['height'].' !important;';
            
            if (!empty($rules['background'])) $ruleStr .= 'background-color:'.$rules['background'].' !important;';
            if (!empty($rules['padding'])) $ruleStr .= 'padding:'.$rules['padding'].' !important;';
            if (!empty($rules['margin'])) $ruleStr .= 'margin:'.$rules['margin'].' !important;';
            if (!empty($rules['border_color']) || !empty($rules['border_width'])) {
                $ruleStr .= 'border:'.($rules['border_width'] ?? '1px').' solid '.($rules['border_color'] ?? '#ccc').' !important;';
            }
            if (!empty($rules['shadow'])) $ruleStr .= 'box-shadow:'.$rules['shadow'].' !important;';
            
            if ($ruleStr !== '') {
                $customCss .= '[data-rh-selectable="'.e($key).'"]{'.$ruleStr.'}';
            }
        }
        if ($customCss !== '') {
            $css .= $customCss;
        }

        $css .= '</style>';

        return preg_replace('/<\/head>/i', $css.'</head>', $html, 1) ?? $css.$html;
    }

    private function resolveCssVariables(string $html, array $theme, ?Template $template): string
    {
        $color = fn (mixed $value, string $fallback): string => is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value) ? $value : $fallback;
        $font = fn (mixed $value, string $fallback): string => in_array($value, ['Inter', 'Roboto', 'Lato', 'Poppins', 'Merriweather'], true) ? $value : $fallback;
        $density = in_array($theme['density'] ?? null, ['compact', 'balanced', 'spacious'], true) ? $theme['density'] : 'balanced';
        $position = in_array($theme['photo_position'] ?? null, ['left', 'center', 'right'], true) ? $theme['photo_position'] : 'center';
        
        $accentColor = $color($theme['accent_color'] ?? null, '#3155e7');
        $secondaryColor = $color($theme['secondary_color'] ?? null, '#142845');
        $pageBg = $color($theme['page_background'] ?? null, '#ffffff');
        
        $margins = ['left' => '0 auto 22px 0', 'center' => '0 auto 22px', 'right' => '0 0 22px auto'];
        $gaps = ['compact' => '14px', 'balanced' => '20px', 'spacious' => '28px'];
        
        $sidebarWidthVal = max(28, min(42, (int) ($theme['sidebar_width'] ?? 34)));
        $sidebarWidth = $sidebarWidthVal . '%';
        $mainWidth = (100 - $sidebarWidthVal) . '%';
        
        $fontScale = (max(80, min(125, (int) ($theme['font_scale'] ?? 100))) / 100);
        $sectionGap = $gaps[$density];
        $dividerDisplay = filter_var($theme['dividers'] ?? true, FILTER_VALIDATE_BOOL) ? 'block' : 'none';
        $photoMargin = $margins[$position];
        
        $headingFont = $font($theme['heading_font'] ?? null, 'Poppins');
        $bodyFont = $font($theme['body_font'] ?? null, 'Inter');
        
        $headerColor = $color($theme['header_color'] ?? null, '#1a252f');
        $headerScale = (max(50, min(200, (int) ($theme['header_scale'] ?? 100))) / 100);
 
        $replacements = [
            '--rh-accent' => $accentColor,
            '--rh-secondary' => $secondaryColor,
            '--rh-page-bg' => $pageBg,
            '--rh-sidebar-width' => $sidebarWidth,
            '--rh-font-scale' => $fontScale,
            '--rh-section-gap' => $sectionGap,
            '--rh-divider-display' => $dividerDisplay,
            '--rh-photo-margin' => $photoMargin,
            '--rh-heading-font' => $headingFont,
            '--rh-body-font' => $bodyFont,
            '--rh-header-color' => $headerColor,
            '--rh-header-scale' => $headerScale,
        ];

        // Replace any CSS variable calls: var(--variable-name, fallback)
        $html = preg_replace_callback('/var\(\s*(--rh-[a-zA-Z0-9_-]+)\s*(?:,\s*([^)]+))?\s*\)/', function ($matches) use ($replacements) {
            $varName = $matches[1];
            $fallback = $matches[2] ?? '';
            return $replacements[$varName] ?? $fallback;
        }, $html);

        // Also replace color-mix that DomPDF fails to parse
        $html = preg_replace('/color-mix\([^)]+\)/i', $accentColor, $html);

        $pageSize = ($theme['page_size'] ?? 'a4') === 'letter' ? 'letter' : 'a4';
        $pageW    = '210mm';
        $pageH    = $pageSize === 'letter' ? '279.4mm' : '297mm';

        $layoutOverrides = '<style>';

        // @page: tells DomPDF the paper size and removes any margins so our
        // template padding controls all spacing.
        $layoutOverrides .= '@page { size: ' . $pageW . ' ' . $pageH . '; margin: 0 !important; } ';

        // Base body/html: explicit width so DomPDF knows the canvas width.
        $layoutOverrides .= 'html, body { width: ' . $pageW . '; margin: 0; padding: 0; } ';

        // rh-page: block, no fixed height — DomPDF must flow content naturally
        // so that the @page breaks work correctly.
        $layoutOverrides .= '.rh-page { display: block !important; width: 100% !important; height: auto !important; min-height: 0 !important; font-size: 0; } ';

        if (($theme['layout'] ?? 'two-column') === 'one-column') {
            // One-column: sidebar is a full-width header, main is below it.
            $layoutOverrides .= '.rh-sidebar { display: block !important; width: 100% !important; min-height: 0 !important; float: none !important; } '
                .'.rh-main { display: block !important; width: 100% !important; float: none !important; } ';
        } else {
            // Two-column: use inline-block with min-height: 297mm so DomPDF fills the A4 page height
            $layoutOverrides .= '.rh-sidebar { display: inline-block !important; vertical-align: top !important; width: ' . $sidebarWidth . ' !important; min-height: ' . $pageH . ' !important; float: none !important; } '
                .'.rh-main { display: inline-block !important; vertical-align: top !important; width: ' . $mainWidth . ' !important; min-height: ' . $pageH . ' !important; float: none !important; } ';
        }

        // Page-break hints — both syntaxes for maximum DomPDF compatibility.
        $layoutOverrides .= '.rh-section { page-break-inside: avoid !important; break-inside: avoid !important; } ';
        $layoutOverrides .= '.rh-item { page-break-inside: avoid !important; break-inside: avoid !important; } ';

        $layoutOverrides .= '</style>';

        $html = preg_replace('/<\/head>/i', $layoutOverrides.'</head>', $html, 1) ?? $html.$layoutOverrides;

        return $html;
    }
}

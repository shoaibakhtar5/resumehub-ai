<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\Template;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class TemplateRenderingService
{
    private const PLACEHOLDERS = [
        'full_name', 'job_title', 'email', 'phone', 'website', 'location', 'photo',
        'summary', 'experiences', 'education', 'skills', 'projects', 'certifications',
        'languages', 'awards', 'references', 'social_links', 'theme_color',
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

        foreach (self::PLACEHOLDERS as $placeholder) {
            $html = preg_replace('/{{\s*'.preg_quote($placeholder, '/').'\s*}}/i', $values[$placeholder] ?? '', $html) ?? $html;
        }

        $fontFamily = data_get($template?->config, 'font_family');
        if (is_string($fontFamily) && preg_match('/^[A-Za-z0-9 ,\-\'\"]+$/', $fontFamily)) {
            $fontStyle = '<style>body{font-family:'.$fontFamily.'}</style>';
            $html = preg_replace('/<\/head>/i', $fontStyle.'</head>', $html, 1) ?? $html;
        }

        return $embedded ? $this->embeddedDocument($html) : $html;
    }

    public function demoPayload(): array
    {
        return [
            'title' => 'Software Engineer Resume',
            'target_role' => 'Senior Software Engineer',
            'profile' => [
                'full_name' => 'Alex Morgan', 'headline' => 'Senior Software Engineer',
                'email' => 'alex.morgan@example.com', 'phone' => '+1 555 014 2288',
                'website' => 'alexmorgan.dev', 'location' => 'San Francisco, CA',
            ],
            'summary' => 'Product-focused engineer with eight years of experience building reliable web applications and leading collaborative teams.',
            'experiences' => [[
                'position' => 'Senior Software Engineer', 'company' => 'Northstar Labs', 'location' => 'San Francisco, CA',
                'start_date' => '2022-01-01', 'is_current' => true,
                'description' => 'Led delivery of customer-facing platforms and improved application performance across critical workflows.',
            ]],
            'educations' => [[
                'degree' => 'BSc Computer Science', 'institution' => 'Pacific University',
                'start_date' => '2013-09-01', 'end_date' => '2017-06-01',
            ]],
            'skills' => [['name' => 'Laravel'], ['name' => 'PHP'], ['name' => 'JavaScript'], ['name' => 'System Design']],
            'projects' => [[
                'name' => 'Workflow Platform', 'role' => 'Technical Lead',
                'description' => 'Designed a secure workflow product used by distributed operations teams.',
            ]],
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
        preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $body);
        $content = $body[1] ?? $html;

        return implode('', array_map(fn (string $css) => '<style>'.$css.'</style>', $styles[1] ?? []))
            .'<div class="rh-embedded-template">'.$content.'</div>';
    }

    private function payloadFromResume(Resume $resume): array
    {
        $resume->loadMissing([
            'profile', 'summary', 'socialLinks', 'experiences', 'educations', 'skills', 'projects',
            'languages', 'certifications', 'awards', 'references', 'template',
        ]);

        return [
            'title' => $resume->title,
            'target_role' => $resume->target_role,
            'profile' => $resume->profile?->toArray() ?? [],
            'summary' => $resume->summary?->content ?? data_get($resume->settings, 'summary', ''),
            'social_links' => $resume->socialLinks->toArray(),
            'experiences' => $resume->experiences->toArray(),
            'educations' => $resume->educations->toArray(),
            'skills' => $resume->skills->map(fn ($item) => ['name' => $item->name, 'proficiency' => $item->pivot?->proficiency, 'is_visible' => $item->pivot?->is_visible ?? true])->all(),
            'projects' => $resume->projects->toArray(),
            'languages' => $resume->languages->map(fn ($item) => ['name' => $item->name, 'proficiency' => $item->pivot?->proficiency, 'is_visible' => $item->pivot?->is_visible ?? true])->all(),
            'certifications' => $resume->certifications->toArray(),
            'awards' => $resume->awards->toArray(),
            'references' => $resume->references->toArray(),
            'theme' => data_get($resume->settings, 'theme', []),
        ];
    }

    private function values(array $payload, ?Template $template, bool $forPdf): array
    {
        $profile = is_array($payload['profile'] ?? null) ? $payload['profile'] : [];
        $summary = $payload['summary'] ?? '';
        if (is_array($summary)) {
            $summary = $summary['content'] ?? '';
        }
        $accent = data_get($payload, 'theme.accent_color') ?: data_get($template?->config, 'primary_color', '#4f46e5');
        $accent = is_string($accent) && preg_match('/^#[0-9a-f]{6}$/i', $accent) ? $accent : '#4f46e5';

        return [
            'full_name' => e($profile['full_name'] ?? $payload['title'] ?? ''),
            'job_title' => e($profile['headline'] ?? $payload['target_role'] ?? ''),
            'email' => e($profile['email'] ?? ''),
            'phone' => e($profile['phone'] ?? ''),
            'website' => e($profile['website'] ?? ''),
            'location' => e($profile['location'] ?? ''),
            'photo' => $this->photo($profile['photo_path'] ?? null, $forPdf),
            'summary' => $this->section('Professional Summary', $this->paragraph($summary)),
            'experiences' => $this->section('Experience', $this->items($payload['experiences'] ?? [], 'experience')),
            'education' => $this->section('Education', $this->items($payload['educations'] ?? $payload['education'] ?? [], 'education')),
            'skills' => $this->section('Skills', $this->tags($payload['skills'] ?? [])),
            'projects' => $this->section('Projects', $this->items($payload['projects'] ?? [], 'project')),
            'certifications' => $this->section('Certifications', $this->items($payload['certifications'] ?? [], 'certification')),
            'languages' => $this->section('Languages', $this->tags($payload['languages'] ?? [], true)),
            'awards' => $this->section('Awards', $this->items($payload['awards'] ?? [], 'award')),
            'references' => $this->section('References', $this->items($payload['references'] ?? [], 'reference')),
            'social_links' => $this->section('Links', $this->tags($payload['social_links'] ?? [], true)),
            'theme_color' => $accent,
        ];
    }

    private function items(array $items, string $type): string
    {
        return collect($items)->filter(fn ($item) => is_array($item) && ($item['is_visible'] ?? true))->map(function (array $item) use ($type): string {
            [$title, $subtitle, $description, $date] = match ($type) {
                'experience' => [$item['position'] ?? '', $item['company'] ?? '', $item['description'] ?? '', $this->dateRange($item)],
                'education' => [$item['degree'] ?? $item['field_of_study'] ?? '', $item['institution'] ?? '', $item['description'] ?? '', $this->dateRange($item)],
                'project' => [$item['name'] ?? '', $item['role'] ?? '', $item['description'] ?? '', $this->dateRange($item)],
                'certification' => [$item['name'] ?? '', $item['issuer'] ?? '', $item['description'] ?? '', $this->date($item['issued_at'] ?? null)],
                'award' => [$item['title'] ?? '', $item['issuer'] ?? '', $item['description'] ?? '', $this->date($item['awarded_at'] ?? null)],
                default => [$item['name'] ?? '', trim(($item['title'] ?? '').' '.($item['company'] ?? '')), '', ''],
            };
            if (! filled($title) && ! filled($subtitle) && ! filled($description)) {
                return '';
            }
            $meta = collect([$subtitle, $item['location'] ?? null])->filter()->map(fn ($value) => e($value))->implode(' · ');

            return '<article class="rh-item"><div class="rh-item-head"><div><strong>'.e($title).'</strong>'.($meta ? '<div class="rh-meta">'.$meta.'</div>' : '').'</div>'.($date ? '<time>'.e($date).'</time>' : '').'</div>'.($description ? '<p>'.nl2br(e($description)).'</p>' : '').'</article>';
        })->filter()->implode('');
    }

    private function tags(array $items, bool $withValue = false): string
    {
        return collect($items)->filter(fn ($item) => is_array($item) && ($item['is_visible'] ?? true) && filled($item['name'] ?? null))->map(function (array $item) use ($withValue): string {
            $value = $withValue ? ($item['proficiency'] ?? $item['url'] ?? '') : '';
            return '<span class="rh-tag">'.e($item['name']).($value ? '<small>'.e($value).'</small>' : '').'</span>';
        })->implode('');
    }

    private function paragraph(mixed $value): string
    {
        return filled($value) ? '<p>'.nl2br(e((string) $value)).'</p>' : '';
    }

    private function section(string $title, string $content): string
    {
        return trim($content) === '' ? '' : '<section class="rh-section"><h2>'.e($title).'</h2>'.$content.'</section>';
    }

    private function photo(mixed $path, bool $forPdf): string
    {
        if (! is_string($path) || trim($path) === '') {
            return '';
        }
        $src = $path;
        if ($forPdf && str_starts_with($path, '/storage/')) {
            $src = public_path(ltrim($path, '/'));
        }

        return '<img class="rh-photo" src="'.e($src).'" alt="Profile photo">';
    }

    private function dateRange(array $item): string
    {
        $start = $this->date($item['start_date'] ?? null);
        $end = filter_var($item['is_current'] ?? false, FILTER_VALIDATE_BOOL) ? 'Present' : $this->date($item['end_date'] ?? null);
        return $start && $end ? $start.' – '.$end : ($start ?: $end);
    }

    private function date(mixed $value): string
    {
        if (! filled($value)) {
            return '';
        }
        try {
            return ($value instanceof DateTimeInterface ? CarbonImmutable::instance($value) : CarbonImmutable::parse($value))->format('M Y');
        } catch (Throwable) {
            return '';
        }
    }
}

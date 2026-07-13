<?php

namespace App\Livewire;

use App\Models\Resume;
use App\Models\Template;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Livewire\Component;
use Throwable;

class LiveResumePreview extends Component
{
    public ?int $resumeId = null;

    public array $payload = [];

    public string $templateSlug = 'modern-professional';

    public string $templateVariant = 'modern';

    public array $templateConfig = [];

    protected $listeners = ['resume-updated' => 'updatePreview'];

    public function mount(?Resume $resume = null): void
    {
        $this->resumeId = $resume?->id;

        if (! $resume) {
            return;
        }

        $resume->loadMissing([
            'profile',
            'summary',
            'socialLinks',
            'experiences',
            'educations',
            'skills',
            'projects',
            'languages',
            'certifications',
            'awards',
            'references',
            'customSections.items',
            'sections',
            'template',
        ]);

        $settings = $resume->settings ?? [];

        $this->payload = [
            'title' => $resume->title,
            'target_role' => $resume->target_role,
            'summary' => $resume->summary?->content ?? ($settings['summary'] ?? ''),
            'profile' => $resume->profile?->toArray() ?? [],
            'theme' => $settings['theme'] ?? [],
            'settings' => $settings,
            'social_links' => $resume->socialLinks->toArray(),
            'experiences' => $resume->experiences->toArray(),
            'educations' => $resume->educations->toArray(),
            'skills' => $resume->skills->map(fn ($skill): array => [
                'name' => $skill->name,
                'category' => $skill->pivot?->category,
                'proficiency' => $skill->pivot?->proficiency,
                'is_visible' => (bool) ($skill->pivot?->is_visible ?? true),
                'sort_order' => (int) ($skill->pivot?->sort_order ?? 0),
            ])->values()->all(),
            'projects' => $resume->projects->toArray(),
            'languages' => $resume->languages->map(fn ($language): array => [
                'name' => $language->name,
                'proficiency' => $language->pivot?->proficiency,
                'is_visible' => (bool) ($language->pivot?->is_visible ?? true),
                'sort_order' => (int) ($language->pivot?->sort_order ?? 0),
            ])->values()->all(),
            'certifications' => $resume->certifications->toArray(),
            'awards' => $resume->awards->toArray(),
            'references' => $resume->references->toArray(),
            'custom_sections' => $resume->customSections->toArray(),
            'sections' => $resume->sections->toArray(),
            'template_id' => $resume->template_id,
        ];

        $this->syncTemplate($resume->template_id, $resume->template);
    }

    public function updatePreview(array $data): void
    {
        $previousTemplateId = $this->payload['template_id'] ?? null;

        // Replace only top-level values supplied by the Builder. This keeps
        // persisted sections that are not part of the active wizard payload.
        $this->payload = array_merge($this->payload, $data);

        $templateId = $this->payload['template_id'] ?? null;

        if ((string) $previousTemplateId !== (string) $templateId) {
            $this->syncTemplate($templateId);
        }
    }

    public function formatDate(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('M Y');
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->format('M Y');
        } catch (Throwable) {
            return null;
        }
    }

    public function formatDateRange(
        array $item,
        string $startKey = 'start_date',
        string $endKey = 'end_date',
        string $currentKey = 'is_current',
    ): ?string {
        $start = $this->formatDate($item[$startKey] ?? null);
        $end = $this->isTruthy($item[$currentKey] ?? false)
            ? 'Present'
            : $this->formatDate($item[$endKey] ?? null);

        if ($start && $end) {
            return $start.' – '.$end;
        }

        return $start ?: $end;
    }

    public function accentColor(): string
    {
        $themeColor = $this->payload['theme']['accent_color'] ?? null;
        $templateColor = $this->templateConfig['accent_color'] ?? null;

        foreach ([$themeColor, $templateColor] as $color) {
            if (is_string($color) && preg_match('/^#[0-9a-f]{6}$/i', $color)) {
                return $color;
            }
        }

        return '#4f46e5';
    }

    public function density(): string
    {
        $requested = $this->payload['theme']['density'] ?? 'balanced';
        $summary = $this->payload['summary'] ?? '';
        $contentLength = strlen(is_array($summary) ? (string) ($summary['content'] ?? '') : (string) $summary);

        foreach (['experiences', 'educations', 'projects', 'certifications', 'awards', 'custom_sections'] as $key) {
            foreach (($this->payload[$key] ?? []) as $item) {
                if (is_array($item)) {
                    $contentLength += strlen(implode(' ', array_filter($item, 'is_string')));
                }
            }
        }

        if ($contentLength > 4200) {
            return 'compact';
        }

        return in_array($requested, ['compact', 'balanced', 'spacious'], true) ? $requested : 'balanced';
    }

    public function render()
    {
        return view('livewire.live-resume-preview', [
            'accent' => $this->accentColor(),
            'density' => $this->density(),
        ]);
    }

    private function syncTemplate(mixed $templateId, ?Template $template = null): void
    {
        if (! $templateId) {
            $this->templateSlug = 'modern-professional';
            $this->templateVariant = 'modern';
            $this->templateConfig = [];

            return;
        }

        $template ??= Template::query()->find($templateId);

        $this->templateSlug = $template?->slug ?: 'modern-professional';
        $this->templateConfig = is_array($template?->config) ? $template->config : [];
        $this->templateVariant = match ($this->templateSlug) {
            'neo-minimalist' => 'neo',
            'executive-flow' => 'executive',
            'syntax-master' => 'technical',
            'standard-global' => 'classic',
            default => 'modern',
        };
    }

    private function isTruthy(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}

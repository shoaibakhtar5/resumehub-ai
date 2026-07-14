<?php

namespace App\Livewire;

use App\Models\Resume;
use App\Models\Template;
use App\Services\TemplateRenderingService;
use Livewire\Component;

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
            'profile', 'summary', 'socialLinks', 'experiences', 'educations', 'skills', 'projects',
            'languages', 'certifications', 'awards', 'references', 'customSections.items', 'sections', 'template',
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
            'skills' => $resume->skills->map(fn ($skill) => [
                'name' => $skill->name, 'category' => $skill->pivot?->category,
                'proficiency' => $skill->pivot?->proficiency, 'is_visible' => (bool) ($skill->pivot?->is_visible ?? true),
            ])->values()->all(),
            'projects' => $resume->projects->toArray(),
            'languages' => $resume->languages->map(fn ($language) => [
                'name' => $language->name, 'proficiency' => $language->pivot?->proficiency,
                'is_visible' => (bool) ($language->pivot?->is_visible ?? true),
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
        // Replace top-level collections so removed entries do not survive.
        $this->payload = array_merge($this->payload, $data);
        $templateId = $this->payload['template_id'] ?? null;
        if ((string) $previousTemplateId !== (string) $templateId) {
            $this->syncTemplate($templateId);
        }
    }

    public function render()
    {
        $template = ($this->payload['template_id'] ?? null) ? Template::query()->find($this->payload['template_id']) : null;

        return view('livewire.live-resume-preview', [
            'renderedHtml' => app(TemplateRenderingService::class)->render($template, $this->payload, false, true),
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
            'neo-minimalist' => 'neo', 'executive-flow' => 'executive',
            'syntax-master' => 'technical', 'standard-global' => 'classic', default => 'modern',
        };
    }
}

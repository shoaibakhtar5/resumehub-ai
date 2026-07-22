<?php

namespace App\Livewire;

use App\Models\Resume;
use App\Services\Ai\AIService;
use Livewire\Component;
use Livewire\Attributes\On;
use Exception;

class AiAssistantPanel extends Component
{
    public ?int $resumeId = null;
    public string $activeSection = 'personal';
    public string $tone = 'professional';
    public string $jobDescription = '';
    public string $input = '';
    public string $suggestion = '';
    public bool $isBusy = false;
    public string $errorMessage = '';
    public string $successMessage = '';
    public array $history = [];

    protected $listeners = [
        'active-section-changed' => 'updateActiveSection'
    ];

    public function mount(?int $resumeId = null): void
    {
        $this->resumeId = $resumeId;
        if ($resumeId) {
            $resume = Resume::find($resumeId);
            if ($resume) {
                // Populate job description from target company/role if available
                $this->jobDescription = $resume->settings['job_description'] ?? '';
            }
        }
    }

    #[On('active-section-changed')]
    public function updateActiveSection(string $section): void
    {
        $this->activeSection = $section;
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->input = '';
        // Clear suggestions when switching sections unless user is in cover letter or general review
        if (!in_array($section, ['cover_letter', 'ats', 'score'], true)) {
            $this->suggestion = '';
            $this->history = [];
        }
    }

    /**
     * Run the AI action.
     *
     * @param string $action
     * @return void
     */
    public function runAction(string $action): void
    {
        $this->isBusy = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $aiService = app(AIService::class);
            
            // Construct input text: if user typed something, use it. Otherwise, pull current section text.
            $promptInput = trim($this->input);
            if (empty($promptInput)) {
                $promptInput = $this->getDefaultInputForSection();
            }

            $historyResult = $aiService->generate(auth()->user(), [
                'resume_id' => $this->resumeId,
                'feature' => 'resume-builder',
                'action' => $action,
                'input' => $promptInput,
                'job_description' => $this->jobDescription,
                'tone' => $this->tone,
            ]);

            // Save current suggestion to history for undo capability
            if (!empty($this->suggestion)) {
                $this->history[] = $this->suggestion;
            }

            $this->suggestion = $historyResult->output;
            $this->successMessage = 'Suggestion generated successfully!';

            // If we have a resume, save job description back to resume settings if updated
            if ($this->resumeId && !empty($this->jobDescription)) {
                $resume = Resume::find($this->resumeId);
                if ($resume) {
                    $settings = $resume->settings ?? [];
                    $settings['job_description'] = $this->jobDescription;
                    $resume->forceFill(['settings' => $settings])->save();
                }
            }

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isBusy = false;
        }
    }

    /**
     * Apply the suggestion back to the resume form.
     */
    public function applySuggestion(): void
    {
        if (empty($this->suggestion)) {
            return;
        }

        // Dispatch browser event that Alpine will catch
        $this->dispatch('ai-suggestion-applied', [
            'section' => $this->activeSection,
            'output' => $this->suggestion,
        ]);

        $this->successMessage = 'Suggestion applied to resume form!';
    }

    /**
     * Undo the suggestion and restore the previous one.
     */
    public function undoSuggestion(): void
    {
        if (!empty($this->history)) {
            $this->suggestion = array_pop($this->history);
            $this->successMessage = 'Restored previous suggestion.';
        } else {
            $this->errorMessage = 'No previous suggestion in history.';
        }
    }

    /**
     * Clear current suggestions and prompt inputs.
     */
    public function clearAll(): void
    {
        $this->suggestion = '';
        $this->input = '';
        $this->history = [];
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    /**
     * Render the Livewire component view.
     */
    public function render()
    {
        return view('livewire.ai-assistant-panel');
    }

    /**
     * Get the default input text for the active section from the database.
     *
     * @return string
     */
    private function getDefaultInputForSection(): string
    {
        if (!$this->resumeId) {
            return '';
        }

        $resume = Resume::find($this->resumeId);
        if (!$resume) {
            return '';
        }

        return match ($this->activeSection) {
            'summary' => $resume->summary?->content ?? '',
            'experience' => $resume->experiences->sortBy('sort_order')->first()?->description ?? '',
            'education' => $resume->educations->sortBy('sort_order')->first()?->description ?? '',
            'projects' => $resume->projects->sortBy('sort_order')->first()?->description ?? '',
            'skills' => $resume->skills->pluck('name')->implode(', '),
            'certifications' => $resume->certifications->sortBy('sort_order')->first()?->name ?? '',
            'awards' => $resume->awards->sortBy('sort_order')->first()?->title ?? '',
            default => '',
        };
    }
}

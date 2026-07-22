<?php

namespace App\Services\Ai;

use App\Models\AiHistory;
use App\Models\AiRequest;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Support\Str;
use Exception;

class AIService
{
    private AiProviderInterface $provider;
    private PromptBuilder $promptBuilder;
    private ResumeContextService $contextService;

    public function __construct(
        PromptBuilder $promptBuilder,
        ResumeContextService $contextService
    ) {
        $this->promptBuilder = $promptBuilder;
        $this->contextService = $contextService;

        // Factory to resolve provider. In the future, this can load other providers from config.
        $this->provider = $this->resolveProvider();
    }

    /**
     * Generate content based on inputs and save it in the database.
     *
     * @param User $user
     * @param array $data
     * @return AiHistory
     * @throws Exception
     */
    public function generate(User $user, array $data): AiHistory
    {
        $resumeId = $data['resume_id'] ?? null;
        $resume = $resumeId ? Resume::find($resumeId) : null;
        
        $input = trim((string) ($data['input'] ?? ''));
        $jobDescription = trim((string) ($data['job_description'] ?? ''));
        $tone = $data['tone'] ?? 'professional';
        $action = $data['action'];
        $feature = $data['feature'] ?? 'resume-builder';

        // 1. Gather resume context
        $resumeContext = '';
        if ($resume) {
            $resumeContext = $this->contextService->getFullResumeContext($resume);
        }

        // 2. Build system and user prompts
        $systemPrompt = $this->promptBuilder->getSystemPrompt($action);
        $userPrompt = $this->promptBuilder->getUserPrompt(
            $action,
            $input,
            $resumeContext,
            $jobDescription,
            $tone
        );

        $source = "System: {$systemPrompt}\n\nUser: {$userPrompt}";
        $promptHash = hash('sha256', $action . '|' . $source);
        $started = microtime(true);

        // 3. Log the start of the AI Request
        $aiRequest = AiRequest::query()->create([
            'user_id' => $user->id,
            'resume_id' => $resume?->id,
            'provider' => config('services.ai.provider', 'gemini'),
            'model' => config('services.ai.model', 'gemini-2.0-flash'),
            'feature' => $feature,
            'action' => $action,
            'prompt_hash' => $promptHash,
            'request_payload' => [
                'input' => $input,
                'job_description' => $jobDescription,
                'tone' => $tone,
                'system_prompt' => $systemPrompt,
                'user_prompt' => $userPrompt,
            ],
            'status' => 'running',
            'requested_at' => now(),
            'input_tokens' => str_word_count($source),
        ]);

        try {
            // 4. Generate the response via the active provider
            $output = $this->provider->generateResponse($systemPrompt, $userPrompt, [
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'timeout' => 45,
            ]);

            // Sanitize response: strip accidental markdown backticks (e.g. ```markdown ... ```)
            $output = $this->sanitizeOutput($output);

            // 5. Update request to completed
            $aiRequest->forceFill([
                'response_payload' => ['output' => $output],
                'status' => 'completed',
                'output_tokens' => str_word_count($output),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'completed_at' => now(),
            ])->save();

            // 6. Save to AiHistory and return
            return AiHistory::query()->create([
                'user_id' => $user->id,
                'resume_id' => $resume?->id,
                'ai_request_id' => $aiRequest->id,
                'title' => Str::headline($action),
                'feature' => $feature,
                'action' => $action,
                'input' => $source,
                'output' => $output,
                'metadata' => [
                    'tone' => $tone,
                    'latency_ms' => $aiRequest->latency_ms
                ],
            ]);

        } catch (Exception $e) {
            // 7. Update request to failed
            $aiRequest->forceFill([
                'response_payload' => ['error' => $e->getMessage()],
                'status' => 'failed',
                'completed_at' => now(),
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
            ])->save();

            throw $e;
        }
    }

    /**
     * Resolve configured provider.
     *
     * @return AiProviderInterface
     */
    protected function resolveProvider(): AiProviderInterface
    {
        $providerName = config('services.ai.provider', 'gemini');

        return match ($providerName) {
            'gemini' => app(GeminiService::class),
            default => app(GeminiService::class), // Fallback to Gemini
        };
    }

    /**
     * Remove wrapping markdown code block indicators if returned by AI.
     *
     * @param string $text
     * @return string
     */
    protected function sanitizeOutput(string $text): string
    {
        $text = trim($text);
        
        // Remove leading ```markdown or ```html or ```text or ```
        if (str_starts_with($text, '```')) {
            $lines = explode("\n", $text);
            if (count($lines) >= 2) {
                // Remove the first line
                array_shift($lines);
                // Remove the last line if it is ```
                if (trim(end($lines)) === '```') {
                    array_pop($lines);
                }
                $text = implode("\n", $lines);
            }
        }
        
        return trim($text);
    }
}

<?php

namespace App\Services\Ai;

use App\Models\AiHistory;
use App\Models\AiRequest;
use App\Models\Resume;
use App\Models\User;
use App\Services\ResumeService;
use Illuminate\Support\Str;

class ResumeAiService
{
    public function __construct(private readonly ResumeService $resumes)
    {
    }

    public function generate(User $user, array $data): AiHistory
    {
        $resume = isset($data['resume_id']) ? Resume::query()->with(['profile', 'experiences', 'educations'])->find($data['resume_id']) : null;
        $input = trim((string) ($data['input'] ?? ''));
        $resumeText = $resume ? $this->resumes->plainText($resume) : '';
        $source = trim($input."\n\n".$resumeText);
        $started = microtime(true);

        $request = AiRequest::query()->create([
            'user_id' => $user->id,
            'resume_id' => $resume?->id,
            'provider' => config('services.ai.provider', 'resumehub-local'),
            'model' => config('services.ai.model', 'heuristic-v1'),
            'feature' => $data['feature'],
            'action' => $data['action'],
            'prompt_hash' => hash('sha256', $data['action'].'|'.$source),
            'request_payload' => $data,
            'status' => 'running',
            'requested_at' => now(),
            'input_tokens' => str_word_count($source),
        ]);

        $output = $this->outputFor($data['action'], $source, (string) ($data['job_description'] ?? ''), (string) ($data['tone'] ?? 'confident'));

        $request->forceFill([
            'response_payload' => ['output' => $output],
            'status' => 'completed',
            'output_tokens' => str_word_count($output),
            'latency_ms' => (int) ((microtime(true) - $started) * 1000),
            'completed_at' => now(),
        ])->save();

        return AiHistory::query()->create([
            'user_id' => $user->id,
            'resume_id' => $resume?->id,
            'ai_request_id' => $request->id,
            'title' => Str::headline($data['action']),
            'feature' => $data['feature'],
            'action' => $data['action'],
            'input' => $source,
            'output' => $output,
            'metadata' => ['tone' => $data['tone'] ?? null],
        ]);
    }

    private function outputFor(string $action, string $source, string $jobDescription, string $tone): string
    {
        $keywords = $this->keywords($jobDescription ?: $source);
        $roleHint = $keywords[0] ?? 'target role';

        return match ($action) {
            'summary' => 'Results-focused '.$roleHint.' professional with proven experience across '.implode(', ', array_slice($keywords, 0, 4)).'. Known for clear execution, measurable outcomes, and cross-functional communication.',
            'experience' => "- Led high-impact initiatives aligned to ".($keywords[0] ?? 'business goals').", improving delivery quality and stakeholder confidence.\n- Converted ambiguous requirements into measurable workstreams with documented outcomes.\n- Partnered across teams to reduce friction, improve adoption, and strengthen operational clarity.",
            'skills' => implode(', ', array_slice(array_unique(array_merge($keywords, ['Leadership', 'Communication', 'Process improvement', 'Stakeholder management'])), 0, 12)),
            'cover_letter' => "Dear Hiring Team,\n\nI am excited to apply for this opportunity because my background aligns strongly with your focus on ".implode(', ', array_slice($keywords, 0, 3)).". I bring a ".$tone." working style, practical judgment, and a record of turning complex goals into polished outcomes.\n\nThank you for your consideration.",
            'interview_questions' => "1. Tell me about a project where you improved ".($keywords[0] ?? 'a key metric').".\n2. How do you prioritize work when requirements are unclear?\n3. What tradeoffs did you make in your most recent role?\n4. How would you approach the first 30 days in this position?\n5. Which achievement best shows your impact?",
            'review' => "Strong foundation. Add two quantified achievements, mirror the top job-description keywords naturally, and keep each bullet focused on action, scope, and result.",
            'score' => 'Estimated resume readiness: '.$this->score($source, $jobDescription).'%. Improve it by adding measurable outcomes and a targeted summary.',
            'keywords' => implode(', ', array_slice($keywords, 0, 18)),
            'ats' => 'ATS recommendation: use standard section labels, include exact role keywords such as '.implode(', ', array_slice($keywords, 0, 6)).', and avoid graphics for critical text.',
            default => 'Generated recommendation based on your resume content and target role.',
        };
    }

    private function keywords(string $text): array
    {
        $stop = array_flip(['and', 'the', 'for', 'with', 'you', 'your', 'our', 'are', 'from', 'that', 'this', 'will', 'have', 'has', 'into', 'role', 'team', 'work']);
        preg_match_all('/[a-zA-Z][a-zA-Z+#.-]{2,}/', Str::lower($text), $matches);

        return collect($matches[0] ?? [])
            ->reject(fn ($word) => isset($stop[$word]))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->map(fn ($word) => Str::headline($word))
            ->values()
            ->all();
    }

    private function score(string $source, string $jobDescription): int
    {
        $resumeKeywords = array_map('strtolower', $this->keywords($source));
        $jobKeywords = array_map('strtolower', array_slice($this->keywords($jobDescription), 0, 20));

        if ($jobKeywords === []) {
            return min(92, 60 + (int) (str_word_count($source) / 12));
        }

        $overlap = count(array_intersect($resumeKeywords, $jobKeywords));

        return min(98, 50 + (int) round(($overlap / max(1, count($jobKeywords))) * 45));
    }
}

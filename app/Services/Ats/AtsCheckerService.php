<?php

namespace App\Services\Ats;

use App\Models\AtsReport;
use App\Models\Resume;
use App\Models\User;
use App\Services\ResumeService;
use Illuminate\Support\Str;

class AtsCheckerService
{
    public function __construct(private readonly ResumeService $resumes)
    {
    }

    public function scan(User $user, array $data): AtsReport
    {
        $resume = isset($data['resume_id']) ? Resume::query()->with(['profile', 'experiences', 'educations'])->find($data['resume_id']) : null;
        $resumeText = trim((string) ($data['resume_text'] ?? ''));

        if ($resume && $resumeText === '') {
            $resumeText = $this->resumes->plainText($resume);
        }

        $jobDescription = trim((string) ($data['job_description'] ?? ''));
        $keywordRows = $this->keywordRows($resumeText, $jobDescription);
        $issues = $this->issues($resumeText, $resume);
        $keywordScore = $this->keywordScore($keywordRows);
        $formattingScore = max(45, 100 - (count(array_filter($issues, fn ($issue) => $issue['category'] === 'formatting')) * 18));
        $contentScore = min(100, 45 + (int) min(45, str_word_count($resumeText) / 10) + ($resume?->experiences->count() ? 10 : 0));
        $readabilityScore = $this->readabilityScore($resumeText);
        $atsScore = round(($keywordScore * 0.35) + ($formattingScore * 0.2) + ($contentScore * 0.3) + ($readabilityScore * 0.15), 2);

        $report = AtsReport::query()->create([
            'user_id' => $user->id,
            'resume_id' => $resume?->id,
            'source' => 'resumehub',
            'target_job_title' => $data['target_job_title'] ?? $resume?->target_role,
            'job_description' => $jobDescription,
            'ats_score' => $atsScore,
            'keyword_score' => $keywordScore,
            'formatting_score' => $formattingScore,
            'content_score' => $contentScore,
            'readability_score' => $readabilityScore,
            'status' => 'completed',
            'raw_result' => [
                'word_count' => str_word_count($resumeText),
                'keywords_checked' => count($keywordRows),
            ],
            'scanned_at' => now(),
        ]);

        foreach ($keywordRows as $row) {
            $report->keywords()->create($row);
        }

        foreach ($issues as $issue) {
            $report->issues()->create($issue);
        }

        return $report->refresh()->load(['keywords', 'issues', 'resume']);
    }

    private function keywordRows(string $resumeText, string $jobDescription): array
    {
        $keywords = array_slice($this->keywords($jobDescription ?: $resumeText), 0, 18);
        $lowerResume = Str::lower($resumeText);

        return collect($keywords)->map(function (string $keyword) use ($lowerResume): array {
            $needle = Str::lower($keyword);
            $occurrences = substr_count($lowerResume, $needle);

            return [
                'keyword' => $keyword,
                'status' => $occurrences > 0 ? 'matched' : 'missing',
                'importance' => $occurrences > 1 ? 'high' : 'medium',
                'occurrences' => $occurrences,
                'suggestion' => $occurrences > 0 ? null : 'Add this keyword naturally where it reflects real experience.',
            ];
        })->all();
    }

    private function issues(string $resumeText, ?Resume $resume): array
    {
        $issues = [];

        if (str_word_count($resumeText) < 180) {
            $issues[] = [
                'category' => 'content',
                'severity' => 'high',
                'title' => 'Resume content is thin',
                'description' => 'ATS systems and recruiters need enough context to match role requirements.',
                'suggestion' => 'Add more role-specific achievements, tools, and measurable outcomes.',
            ];
        }

        if (! preg_match('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', $resumeText)) {
            $issues[] = [
                'category' => 'formatting',
                'severity' => 'medium',
                'title' => 'Email address not detected',
                'description' => 'Contact details should be parseable as plain text.',
                'suggestion' => 'Place your email near the top of the resume.',
            ];
        }

        if (! $resume?->experiences?->count()) {
            $issues[] = [
                'category' => 'content',
                'severity' => 'medium',
                'title' => 'Experience section needs structure',
                'description' => 'Structured experience improves both review speed and ATS matching.',
                'suggestion' => 'Add company, role, dates, and outcome-oriented bullets.',
            ];
        }

        if (! Str::contains(Str::lower($resumeText), ['experience', 'education', 'skills'])) {
            $issues[] = [
                'category' => 'formatting',
                'severity' => 'low',
                'title' => 'Standard section labels are missing',
                'description' => 'ATS parsing is more reliable when common labels are present.',
                'suggestion' => 'Use headings like Experience, Education, and Skills.',
            ];
        }

        return $issues;
    }

    private function keywordScore(array $keywordRows): float
    {
        if ($keywordRows === []) {
            return 78;
        }

        $matched = count(array_filter($keywordRows, fn ($row) => $row['status'] === 'matched'));

        return round(($matched / count($keywordRows)) * 100, 2);
    }

    private function readabilityScore(string $resumeText): int
    {
        $words = max(1, str_word_count($resumeText));
        $sentences = max(1, preg_match_all('/[.!?]+/', $resumeText) ?: 1);
        $average = $words / $sentences;

        return (int) max(55, min(96, 100 - abs($average - 18)));
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
}

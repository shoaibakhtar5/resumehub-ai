<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use ZipArchive;

class ResumeImportService
{
    public function __construct(
        private readonly ResumeBuilderService $builder,
        private readonly ResumeService $resumes
    ) {}

    public function import(User $user, UploadedFile $file, array $data = []): Resume
    {
        $text = $this->extractText($file);
        $payload = $this->payloadFromText($text, $data, $file);

        return $this->resumes->create($user, $this->builder->buildPayload($payload));
    }

    private function extractText(UploadedFile $file): string
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        $text = match ($extension) {
            'docx' => $this->extractDocxText($file),
            'pdf' => $this->extractPdfText($file),
            'txt' => (string) file_get_contents($file->getRealPath()),
            default => '',
        };

        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode($text, ENT_QUOTES | ENT_XML1)));

        if (str_word_count($text) < 8) {
            throw ValidationException::withMessages([
                'resume_file' => 'The uploaded resume did not contain enough extractable text.',
            ]);
        }

        return $text;
    }

    private function extractDocxText(UploadedFile $file): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The Zip extension is required to import DOCX resumes.');
        }

        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            throw ValidationException::withMessages([
                'resume_file' => 'The DOCX file could not be opened.',
            ]);
        }

        $document = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        $document = preg_replace('/<\/w:p>/', "\n", $document) ?? $document;

        return strip_tags($document);
    }

    private function extractPdfText(UploadedFile $file): string
    {
        $contents = (string) file_get_contents($file->getRealPath());
        preg_match_all('/\(([^()]{2,})\)/', $contents, $matches);

        $text = implode(' ', $matches[1] ?? []);
        $text = preg_replace('/\\\\([()\\\\])/', '$1', $text) ?? $text;

        return preg_replace('/[^[:print:]\s]/', ' ', $text) ?? '';
    }

    private function payloadFromText(string $text, array $data, UploadedFile $file): array
    {
        $lines = collect(preg_split('/\r\n|\r|\n|(?<=\.)\s+/', $text) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->values();

        $email = $this->match('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', $text);
        $phone = $this->match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text);
        $website = $this->match('/https?:\/\/[^\s]+/i', $text);
        $name = $lines->first(fn (string $line): bool => ! Str::contains(Str::lower($line), ['@', 'http', 'resume', 'curriculum'])) ?: $file->getClientOriginalName();
        $skills = $this->sectionTerms($text, ['skills', 'technical skills', 'core skills']);
        $languages = $this->sectionTerms($text, ['languages']);
        $certifications = $this->sectionLines($text, ['certifications', 'licenses'])->map(fn (string $line): array => ['name' => $line])->all();
        $projects = $this->sectionLines($text, ['projects'])->take(3)->map(fn (string $line): array => ['name' => Str::limit($line, 120, ''), 'description' => $line])->all();

        return [
            'title' => ($data['title'] ?? null) ?: Str::of($file->getClientOriginalName())->beforeLast('.')->headline()->value(),
            'source' => 'import',
            'target_role' => $data['target_role'] ?? null,
            'summary' => Str::limit($text, 900, ''),
            'profile' => [
                'full_name' => Str::limit($name, 120, ''),
                'email' => $email,
                'phone' => $phone,
                'website' => $website,
                'metadata' => [
                    'imported_filename' => $file->getClientOriginalName(),
                ],
            ],
            'skills' => $skills,
            'languages' => $languages,
            'projects' => $projects,
            'certifications' => $certifications,
            'custom_sections' => [[
                'title' => 'Imported Resume Text',
                'description' => Str::limit($text, 2500, ''),
                'is_visible' => false,
                'sort_order' => 99,
            ]],
            'import' => [
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'imported_at' => now()->toIso8601String(),
            ],
        ];
    }

    private function sectionTerms(string $text, array $headings): array
    {
        return $this->sectionLines($text, $headings)
            ->flatMap(fn (string $line): array => preg_split('/[,;|]/', $line) ?: [])
            ->map(fn (string $term): string => trim($term))
            ->filter(fn (string $term): bool => filled($term) && str_word_count($term) <= 5)
            ->take(20)
            ->values()
            ->all();
    }

    private function sectionLines(string $text, array $headings)
    {
        $pattern = '/(?:^|\n)\s*('.implode('|', array_map('preg_quote', $headings)).')\s*[:\n-]+(.+?)(?=\n\s*[A-Z][A-Za-z ]{2,}\s*[:\n-]+|\z)/is';

        if (! preg_match($pattern, $text, $match)) {
            return collect();
        }

        return collect(preg_split('/\r\n|\r|\n|[\x{2022}\-]\s+/u', $match[2]) ?: [])
            ->map(fn (string $line): string => trim($line, " \t\n\r\0\x0B-*"))
            ->filter()
            ->values();
    }

    private function match(string $pattern, string $text): ?string
    {
        preg_match($pattern, $text, $match);

        return $match[0] ?? null;
    }
}

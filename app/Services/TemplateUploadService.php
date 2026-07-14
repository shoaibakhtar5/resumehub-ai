<?php

namespace App\Services;

use App\Models\Template;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TemplateUploadService
{
    public function __construct(
        private readonly TemplateRenderingService $renderer,
        private readonly MediaService $media,
    ) {}

    public function validateSource(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['html', 'htm', 'txt'], true)) {
            throw ValidationException::withMessages(['template_file' => 'Upload an HTML file or a TXT file containing HTML markup.']);
        }

        $html = file_get_contents($file->getRealPath());

        if (! is_string($html) || trim($html) === '' || ! preg_match('/<\s*[a-z][^>]*>/i', $html)) {
            throw ValidationException::withMessages(['template_file' => 'The uploaded file does not contain valid HTML markup.']);
        }

        if (preg_match('/<(script|iframe|object|embed|form|base)\b/i', $html)
            || preg_match('/\son[a-z]+\s*=/i', $html)
            || preg_match('/(?:javascript:|data:text\/html|@import|expression\s*\()/i', $html)) {
            throw ValidationException::withMessages(['template_file' => 'The template contains unsafe executable or embedded content.']);
        }

        preg_match_all('/{{\s*([a-z_]+)\s*}}/i', $html, $matches);
        $placeholders = array_values(array_unique(array_map('strtolower', $matches[1] ?? [])));
        $unknown = array_diff($placeholders, $this->renderer->allowedPlaceholders());

        if ($unknown !== []) {
            throw ValidationException::withMessages(['template_file' => 'Unknown placeholder(s): '.implode(', ', $unknown).'.']);
        }

        if (! in_array('full_name', $placeholders, true)) {
            throw ValidationException::withMessages(['template_file' => 'The template must contain the {{ full_name }} placeholder.']);
        }

        $contentPlaceholders = array_intersect($placeholders, ['summary', 'experiences', 'education', 'skills', 'projects', 'certifications', 'languages', 'awards', 'references']);

        if ($contentPlaceholders === []) {
            throw ValidationException::withMessages(['template_file' => 'Include at least one resume section placeholder, such as {{ experiences }} or {{ education }}.']);
        }

        return $this->normalizeDocument($html);
    }

    public function storeSource(Template $template, UploadedFile $file): string
    {
        $html = $this->validateSource($file);
        $version = $this->nextVersion($template->version);
        $path = 'templates/'.$template->getKey().'/'.$version.'/resume.html';

        Storage::disk('local')->put($path, $html);
        $template->forceFill([
            'package_path' => $path,
            'entry_html' => 'resume.html',
            'version' => $version,
        ])->save();

        return $path;
    }

    public function storeThumbnail(Template $template, UploadedFile $file, User $user): void
    {
        $media = $this->media->store($file, 'template-thumbnails', $user, $template, [
            'alt_text' => $template->name.' template preview',
        ]);

        $template->forceFill([
            'preview_media_id' => $media->getKey(),
            'preview_path' => data_get($media->metadata, 'url'),
        ])->save();
    }

    public function deleteSource(Template $template): void
    {
        if ($template->package_path) {
            Storage::disk('local')->delete($template->package_path);
        }
    }

    private function normalizeDocument(string $html): string
    {
        $html = str_replace(["\r\n", "\r"], "\n", trim($html));

        if (! preg_match('/<html\b/i', $html)) {
            $html = '<!doctype html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>';
        }

        return $html;
    }

    private function nextVersion(?string $version): string
    {
        $parts = array_map('intval', explode('.', $version ?: '0.0.0'));
        $parts = array_pad($parts, 3, 0);
        $parts[2]++;

        return implode('.', array_slice($parts, 0, 3));
    }
}

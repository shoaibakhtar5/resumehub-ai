<?php

namespace App\Services;

use App\Models\Template;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use RuntimeException;

class TemplateUploadService
{
    public function __construct(
        private readonly TemplateProcessingService $processor,
        private readonly MediaService $media,
    ) {}

    public function validateSource(UploadedFile $file): string
    {
        return $this->processor->process($file)['html'];
    }

    public function storeSource(Template $template, UploadedFile $file): string
    {
        $processed = $this->processor->process($file);
        $version = $this->nextVersion($template->version);
        $path = 'templates/'.$template->getKey().'/'.$version.'/resume.html';
        $previousPath = $template->package_path;

        if (! Storage::disk('local')->put($path, $processed['html'])) {
            throw new RuntimeException('The processed template could not be stored.');
        }
        $template->forceFill([
            'package_path' => $path,
            'entry_html' => 'resume.html',
            'version' => $version,
            'config' => array_merge($template->config ?? [], [
                'source_type' => $processed['source_type'],
                'source_extension' => $processed['extension'],
                'source_mime_type' => $processed['mime_type'],
                'source_original_name' => $processed['original_name'],
                'source_size_bytes' => $processed['size_bytes'],
                'source_checksum' => $processed['checksum'],
                'detected_placeholders' => $processed['placeholders'],
                'detected_fields' => $processed['detected_fields'],
                'mapping_candidates' => $processed['mapping_candidates'],
                'requires_mapping' => $processed['requires_mapping'],
                'processed_at' => Carbon::now()->toIso8601String(),
                'processor_version' => 2,
            ]),
            'status' => $processed['requires_mapping'] ? 'draft' : $template->status,
        ])->save();

        if ($previousPath && $previousPath !== $path) {
            Storage::disk('local')->delete($previousPath);
        }

        return $path;
    }

    /** @param array<string,string|null> $mappings */
    public function applyMappings(Template $template, array $mappings): void
    {
        $candidates = data_get($template->config, 'mapping_candidates', []);
        if (! is_array($candidates) || $candidates === [] || ! $template->package_path) {
            return;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($template->package_path)) {
            throw new RuntimeException('The stored template source could not be found.');
        }

        $processed = $this->processor->applyMappings($disk->get($template->package_path), $mappings, $candidates);
        $version = $this->nextVersion($template->version);
        $path = 'templates/'.$template->getKey().'/'.$version.'/resume.html';
        $previousPath = $template->package_path;
        if (! $disk->put($path, $processed['html'])) {
            throw new RuntimeException('The mapped template could not be stored.');
        }

        $template->forceFill([
            'package_path' => $path,
            'version' => $version,
            'status' => $processed['requires_mapping'] ? 'draft' : $template->status,
            'config' => array_merge($template->config ?? [], [
                'detected_placeholders' => $processed['placeholders'],
                'mapping_candidates' => $processed['mapping_candidates'],
                'requires_mapping' => $processed['requires_mapping'],
                'processed_at' => Carbon::now()->toIso8601String(),
            ]),
        ])->save();

        if ($previousPath !== $path) {
            $disk->delete($previousPath);
        }
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

    private function nextVersion(?string $version): string
    {
        $parts = array_map('intval', explode('.', $version ?: '0.0.0'));
        $parts = array_pad($parts, 3, 0);
        $parts[2]++;

        return implode('.', array_slice($parts, 0, 3));
    }
}

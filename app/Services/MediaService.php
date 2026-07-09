<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function store(UploadedFile $file, string $directory, ?User $user = null, ?Model $mediable = null, array $metadata = []): Media
    {
        $disk = 'public';
        $path = $file->store($directory, $disk);
        $absolutePath = Storage::disk($disk)->path($path);
        $imageSize = @getimagesize($absolutePath) ?: null;

        return Media::query()->create([
            'uploaded_by_user_id' => $user?->id,
            'mediable_type' => $mediable ? $mediable::class : null,
            'mediable_id' => $mediable?->getKey(),
            'disk' => $disk,
            'directory' => str_replace('\\', '/', dirname($path)),
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $file->getClientOriginalExtension() ?: $file->extension(),
            'size_bytes' => $file->getSize() ?: 0,
            'checksum' => hash_file('sha256', $absolutePath),
            'visibility' => 'public',
            'width' => $imageSize[0] ?? null,
            'height' => $imageSize[1] ?? null,
            'alt_text' => $metadata['alt_text'] ?? Str::headline(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'metadata' => $metadata + ['path' => $path, 'url' => Storage::disk($disk)->url($path)],
        ]);
    }
}

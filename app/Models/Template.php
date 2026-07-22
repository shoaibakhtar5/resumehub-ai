<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_premium' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function previewMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'preview_media_id');
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getIsFeaturedAttribute(): bool
    {
        return (bool) data_get($this->config, 'is_featured', false);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return data_get($this->previewMedia?->metadata, 'url') ?: $this->preview_path;
    }

    public function getSourceTypeAttribute(): string
    {
        return (string) data_get($this->config, 'source_type', 'html');
    }
}

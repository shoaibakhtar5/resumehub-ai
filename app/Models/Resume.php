<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
            'last_autosaved_at' => 'datetime',
            'last_exported_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ResumeProfile::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(ResumeExperience::class)->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(ResumeEducation::class)->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class)->latest('version_number');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ResumeShare::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ResumeDownload::class);
    }

    public function atsReports(): HasMany
    {
        return $this->hasMany(AtsReport::class);
    }
}

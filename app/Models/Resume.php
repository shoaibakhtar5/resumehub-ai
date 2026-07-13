<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function summary(): HasOne
    {
        return $this->hasOne(ResumeSummary::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(ResumeExperience::class)->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(ResumeEducation::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(ResumeProject::class)->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(ResumeCertification::class)->orderBy('sort_order');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(ResumeAward::class)->orderBy('sort_order');
    }

    public function references(): HasMany
    {
        return $this->hasMany(ResumeReference::class)->orderBy('sort_order');
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(ResumeSocialLink::class)->orderBy('sort_order');
    }

    public function customSections(): HasMany
    {
        return $this->hasMany(ResumeCustomSection::class)->orderBy('sort_order');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'resume_skill')
            ->withPivot(['category', 'proficiency', 'years_experience', 'is_visible', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'resume_language')
            ->withPivot(['proficiency', 'is_visible', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ResumeSection::class)->orderBy('sort_order');
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

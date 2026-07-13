<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResumeCustomSectionItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'fields' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function customSection(): BelongsTo
    {
        return $this->belongsTo(ResumeCustomSection::class, 'resume_custom_section_id');
    }
}

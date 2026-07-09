<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResumeShare extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'allow_download' => 'boolean',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}

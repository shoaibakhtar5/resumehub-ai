<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtsReport extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'raw_result' => 'array',
            'scanned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(AtsKeyword::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(AtsIssue::class);
    }
}

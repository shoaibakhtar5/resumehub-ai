<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeSocialLink extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}

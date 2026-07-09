<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeVersion extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'metadata' => 'array',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}

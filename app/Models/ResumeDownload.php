<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeDownload extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['downloaded_at' => 'datetime'];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}

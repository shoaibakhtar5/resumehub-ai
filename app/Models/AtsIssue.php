<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtsIssue extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(AtsReport::class, 'ats_report_id');
    }
}

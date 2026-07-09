<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtsKeyword extends Model
{
    protected $guarded = [];

    public function report(): BelongsTo
    {
        return $this->belongsTo(AtsReport::class, 'ats_report_id');
    }
}

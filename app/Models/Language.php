<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function resumes(): BelongsToMany
    {
        return $this->belongsToMany(Resume::class, 'resume_language')
            ->withPivot(['proficiency', 'is_visible', 'sort_order'])
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogTag extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_tag')->withTimestamps();
    }
}

<?php

namespace App\Services;

use App\Models\Resume;
use Carbon\Carbon;

class AutoSaveService
{
    public function shouldAutoSave(Resume $resume): bool
    {
        if (! $resume->last_autosaved_at) {
            return true;
        }

        return $resume->last_autosaved_at->lt(Carbon::now()->subSeconds(5));
    }
}

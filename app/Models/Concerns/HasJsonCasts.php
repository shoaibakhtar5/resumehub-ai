<?php

namespace App\Models\Concerns;

trait HasJsonCasts
{
    protected function jsonCasts(array $columns): array
    {
        return array_fill_keys($columns, 'array');
    }
}

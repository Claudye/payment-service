<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterByRequest
{
    public function scopeRequested(Builder $query, $keys = ["*"], array $options = [])
    {
        $first = $keys[0] ?? "*";
        $filters = $first == '*' ? request()->all() : request()->only($keys);

        # Vérifie d'abord si on peut filtrer
        if ($this->hasNamedScope("filter")) {
            $query->filter($filters + $options);
        }
    }
}

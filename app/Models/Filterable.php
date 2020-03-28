<?php

namespace App\Models;

use App\Http\Requests\Filters\FilterRequest;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * @param Builder $query
     * @param FilterRequest $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, FilterRequest $filters): Builder
    {
        return $filters->apply($query);
    }
}

<?php


namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * @param Builder $query
     * @param Filter $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, Filter $filters): Builder
    {
        return $filters->apply($query);
    }
}

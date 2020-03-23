<?php


namespace App\Http\Requests\Filters;

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

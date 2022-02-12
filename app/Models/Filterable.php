<?php

namespace App\Models;

use App\Http\Requests\Filters\FilterRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait Filterable
{
    /**
     * @param Builder<Model> $query
     * @param FilterRequest $filters
     * @return Builder<Model>
     */
    public function scopeFilter(Builder $query, FilterRequest $filters): Builder
    {
        return $filters->apply($query);
    }
}

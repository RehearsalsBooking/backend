<?php

namespace App\Http\Requests\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Class for filtering models.
 *
 * apply method is called in scopeFilter of the Filterable trait.
 * all filtration logic and request validation is defined in concrete class implementation
 *
 * @property Request $request
 * @property Builder $builder
 */
abstract class FilterRequest
{
    public Request $request;
    protected Builder $builder;

    public function __construct(Request $request)
    {
        $request->validate($this->getRules());
        $this->request = $request;
    }

    abstract protected function getRules(): array;

    /**
     * Applies filters from request to query.
     *
     * @param $builder
     * @return Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;
        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    /**
     * Returns filters from request.
     *
     * @return array
     */
    protected function getFilters(): array
    {
        return $this->request->only(array_keys($this->getRules()));
    }
}

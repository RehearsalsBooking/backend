<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Filters\FilterRequest;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Illuminate\Database\Eloquent\Builder;

class OrganizationsFilterRequest extends FilterRequest
{
    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after:from',
            'name' => 'sometimes|string',
            'favorite' => 'sometimes|bool',
        ];
    }

    /**
     * @return array
     */
    protected function getFilters(): array
    {
        $filters = parent::getFilters();

        if ($this->filteringByAvailableTime()) {
            return array_merge(
                $filters,
                [
                    'available_time' => [
                        $this->request->get('from'),
                        $this->request->get('to'),
                    ],
                ]
            );
        }

        return $filters;
    }

    /**
     * @return bool
     */
    protected function filteringByAvailableTime(): bool
    {
        return $this->request->has('from') || $this->request->has('to');
    }

    /**
     * @param $boundaries
     */
    protected function available_time($boundaries): void
    {
        [$from, $to] = $boundaries;

        $range = new TimestampRange($from, $to, '(', ')');

        $this->builder->whereDoesntHave('rehearsals', static function (Builder $builder) use ($range) {
            $builder->whereRaw('time && ?::tsrange', [$range]);
        });
    }

    /**
     * @param  string  $name
     */
    protected function name(string $name): void
    {
        $this->builder->where('name', 'like', "%$name%");
    }

    /**
     * @param  bool  $isApplied
     */
    protected function favorite(bool $isApplied): void
    {
        if ($isApplied && auth()->check()) {
            $this->builder->whereHas(
                'favoritedUsers',
                fn(Builder $query) => $query->where('user_id', auth()->id())
            );
        }
    }
}

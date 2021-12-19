<?php

namespace App\Http\Requests\Filters;

use Belamov\PostgresRange\Ranges\TimestampRange;
use Illuminate\Database\Eloquent\Builder;

class OrganizationsFilterRequest extends FilterRequest
{
    protected function getRules(): array
    {
        return [
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after:from',
            'name' => 'sometimes|string',
            'favorite' => 'sometimes|bool',
            'city_id' => 'sometimes|int|exists:cities,id'
        ];
    }

    protected function getFilters(): array
    {
        $filters = parent::getFilters();

        if ($this->filteringByAvailableTime()) {
            $filters = array_merge(
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

    protected function filteringByAvailableTime(): bool
    {
        return $this->request->has('from') || $this->request->has('to');
    }

    protected function available_time(array $boundaries): void
    {
        [$from, $to] = $boundaries;

        $range = new TimestampRange($from, $to, '(', ')');

        $this->builder->whereDoesntHave('rehearsals', static function (Builder $builder) use ($range) {
            $builder->whereRaw('time && ?::tsrange', [$range]);
        });
    }

    protected function name(string $name): void
    {
        $this->builder->where('name', 'like', "%$name%");
    }

    protected function favorite(bool $isApplied): void
    {
        if ($isApplied && auth()->check()) {
            $this->builder->whereHas(
                'favoritedUsers',
                fn(Builder $query) => $query->where('user_id', auth()->id())
            );
        }
    }

    protected function city_id(int $cityId): void
    {
        $this->builder->where('city_id', $cityId);
    }
}

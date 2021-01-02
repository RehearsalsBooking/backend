<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Ranges\DateRange;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class OrganizationStatistics
{
    protected Closure $setInterval;

    public function __construct(protected Organization $organization, protected ?DateRange $interval)
    {
        $this->setInterval = fn(Builder $query) => $query->whereRaw('time <@ ?', [$this->interval]);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->organization->rehearsals()
            ->selectRaw('sum(price) as income, count(*) as count')
            ->when($this->interval !== null, $this->setInterval)
            ->get()
            ->toArray();
    }
}

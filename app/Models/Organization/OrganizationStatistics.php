<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Ranges\DateRange;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class OrganizationStatistics
{
    protected Organization $organization;
    protected ?DateRange $interval;
    protected array $rooms;
    protected Closure $filterByInterval;
    protected Closure $filterByRoom;

    public function __construct(
        Organization $organization,
        ?DateRange $interval,
        array $rooms = []
    ) {
        $this->organization = $organization;
        $this->interval = $interval;
        $this->rooms = $rooms;
        $this->filterByInterval = static fn(Builder $query) => $query->whereRaw('time <@ ?', [$interval]);
        $this->filterByRoom = static fn(Builder $query) => $query->whereIn('organization_room_id', $rooms);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->organization->rehearsals()
            ->selectRaw('sum(price) as income, count(*) as count')
            ->when($this->interval !== null, $this->filterByInterval)
            ->when(count($this->rooms) > 0, $this->filterByRoom)
            ->groupBy('organization_id')
            ->get()
            ->toArray();
    }
}

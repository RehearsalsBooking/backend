<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Ranges\DateRange;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class OrganizationStatistics
{
    protected Organization $organization;
    protected ?DateRange $interval;
    protected ?int $roomId;
    protected Closure $filterByInterval;
    protected Closure $filterByRoom;

    public function __construct(
        Organization $organization,
        ?DateRange $interval,
        int $roomId = null
    ) {
        $this->organization = $organization;
        $this->interval = $interval;
        $this->roomId = $roomId;
        $this->filterByInterval = static fn(Builder $query) => $query->whereRaw('time <@ ?', [$interval]);
        $this->filterByRoom = static fn(Builder $query) => $query->where('organization_room_id', $roomId);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->organization->rehearsals()
            ->selectRaw('sum(price) as income, count(*) as count')
            ->when($this->interval !== null, $this->filterByInterval)
            ->when($this->roomId !== null, $this->filterByRoom)
            ->groupBy('organization_id')
            ->get()
            ->toArray();
    }
}

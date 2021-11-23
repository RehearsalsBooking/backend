<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Ranges\DateRange;

class OrganizationStatisticsGrouped extends OrganizationStatistics
{
    private string $groupInterval;

    public function __construct(
        Organization $organization,
        ?DateRange $interval,
        array $rooms,
        string $groupInterval
    ) {
        parent::__construct($organization, $interval, $rooms);
        $this->groupInterval = $groupInterval;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->organization->rehearsals()
            ->selectRaw('date_trunc(?, lower(time)) as x', [$this->groupInterval])
            ->selectRaw('count(*) as count')
            ->selectRaw('sum(price) as income')
            ->when($this->interval !== null, $this->filterByInterval)
            ->when(count($this->rooms) > 0, $this->filterByRoom)
            ->groupBy('x', 'organization_id')
            ->orderBy('x')
            ->get()
            ->toArray();
    }
}

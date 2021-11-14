<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Ranges\DateRange;

class OrganizationStatisticsGrouped extends OrganizationStatistics
{
    private string $groupInterval;

    public function __construct(
        Organization $organization,
        ?DateRange $interval,
        ?int $roomId,
        string $groupInterval
    ) {
        parent::__construct($organization, $interval, $roomId);
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
            ->when($this->roomId !== null, $this->filterByRoom)
            ->groupBy('x', 'organization_id')
            ->orderBy('x')
            ->get()
            ->toArray();
    }
}

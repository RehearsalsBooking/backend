<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Ranges\DateRange;

class OrganizationStatisticsGrouped extends OrganizationStatistics
{
    public function __construct(Organization $organization, ?DateRange $interval, protected string $groupInterval)
    {
        parent::__construct($organization, $interval);
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
            ->when($this->interval !== null, $this->setInterval)
            ->groupBy('x', 'organization_id')
            ->orderBy('x')
            ->get()
            ->toArray();
    }
}

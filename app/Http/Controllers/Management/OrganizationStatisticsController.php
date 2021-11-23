<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\OrganizationStatisticsGroupedRequest;
use App\Http\Requests\Management\OrganizationStatisticsRequest;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationStatistics;
use App\Models\Organization\OrganizationStatisticsGrouped;
use Illuminate\Auth\Access\AuthorizationException;

class OrganizationStatisticsController extends Controller
{
    /**
     * @param  OrganizationStatisticsRequest  $request
     * @param  Organization  $organization
     * @return array
     * @throws AuthorizationException
     */
    public function total(OrganizationStatisticsRequest $request, Organization $organization): array
    {
        $this->authorize('manage', $organization);

        $statistics = new OrganizationStatistics($organization, $request->interval(), $request->rooms());

        return $statistics->get();
    }

    /**
     * @param  OrganizationStatisticsGroupedRequest  $request
     * @param  Organization  $organization
     * @return array
     * @throws AuthorizationException
     */
    public function grouped(OrganizationStatisticsGroupedRequest $request, Organization $organization): array
    {
        $this->authorize('manage', $organization);

        $statistics = new OrganizationStatisticsGrouped(
            $organization,
            $request->interval(),
            $request->rooms(),
            $request->groupInterval()
        );

        return $statistics->get();
    }
}

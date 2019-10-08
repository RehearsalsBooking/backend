<?php

namespace App\Http\Controllers\Users;

use App\Http\Requests\Users\CreateRehearsalRequest;
use App\Http\Requests\Users\RehearsalDeleteRequest;
use App\Http\Requests\Users\RescheduleRehearsalRequest;
use App\Http\Resources\Users\RehearsalResource;
use App\Filters\RehearsalsFilterRequest;
use App\Models\Organization;
use App\Http\Controllers\Controller;
use App\Models\Rehearsal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrganizationRehearsalsController extends Controller
{
    /**
     * @param RehearsalsFilterRequest $filter
     * @param Organization $organization
     * @return AnonymousResourceCollection
     */
    public function index(RehearsalsFilterRequest $filter, Organization $organization): AnonymousResourceCollection
    {
        $rehearsals = $organization
            ->rehearsals()
            ->filter($filter)
            ->with(['user'])
            ->get();

        return RehearsalResource::collection($rehearsals);
    }

    /**
     * @param CreateRehearsalRequest $request
     * @param Organization $organization
     * @return RehearsalResource|JsonResponse
     */
    public function create(CreateRehearsalRequest $request, Organization $organization)
    {
        if (!$organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
        )) {
            return response()->json('Selected time is unavailable', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var Rehearsal $rehearsal */
        $rehearsal = $organization->rehearsals()->create($request->getAttributes());

        if ($request->onBehalfOfTheBand()) {
            $rehearsal->registerBandMembersAsAttendees();
        } else {
            $rehearsal->registerUserAsAttendee();
        }

        return new RehearsalResource($rehearsal);
    }

    public function reschedule(RescheduleRehearsalRequest $request, Organization $organization, Rehearsal $rehearsal)
    {
        if (!$organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
            $rehearsal
        )) {
            return response()->json('Selected time is unavailable', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rehearsal->update($request->getRehearsalAttributes());

        return new RehearsalResource($rehearsal);
    }
}

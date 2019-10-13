<?php

namespace App\Http\Controllers\Users;

use App\Filters\RehearsalsFilterRequest;
use App\Http\Requests\Users\CreateRehearsalRequest;
use App\Http\Requests\Users\RescheduleRehearsalRequest;
use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RehearsalsController extends Controller
{

    /**
     * @param RehearsalsFilterRequest $filter
     * @return AnonymousResourceCollection
     */
    public function index(RehearsalsFilterRequest $filter): AnonymousResourceCollection
    {
        $rehearsals = Rehearsal::filter($filter)
            ->with(['user'])
            ->get();

        return RehearsalResource::collection($rehearsals);
    }

    /**
     * @param CreateRehearsalRequest $request
     * @return RehearsalResource|JsonResponse
     */
    public function create(CreateRehearsalRequest $request)
    {
        /** @noinspection NullPointerExceptionInspection */
        if (!$request->organization()->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
        )) {
            return response()->json('Selected time is unavailable', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var Rehearsal $rehearsal */
        $rehearsal = Rehearsal::create($request->getAttributes());

        if ($request->onBehalfOfTheBand()) {
            $rehearsal->registerBandMembersAsAttendees();
        } else {
            $rehearsal->registerUserAsAttendee();
        }

        return new RehearsalResource($rehearsal);
    }

    /**
     * @param RescheduleRehearsalRequest $request
     * @param Rehearsal $rehearsal
     * @return RehearsalResource|JsonResponse
     */
    public function reschedule(RescheduleRehearsalRequest $request, Rehearsal $rehearsal)
    {
        if (!$rehearsal->organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
            $rehearsal
        )) {
            return response()->json('Selected time is unavailable', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rehearsal->update($request->getRehearsalAttributes());

        return new RehearsalResource($rehearsal);
    }

    /**
     * @param Rehearsal $rehearsal
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(Rehearsal $rehearsal): JsonResponse
    {
        $rehearsal->delete();

        return response()->json('rehearsal successfully deleted');
    }
}

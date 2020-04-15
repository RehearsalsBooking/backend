<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CreateRehearsalRequest;
use App\Http\Requests\Users\RehearsalsFilterClientRequest;
use App\Http\Requests\Users\RescheduleRehearsalRequest;
use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RehearsalsController extends Controller
{
    /**
     * @param  RehearsalsFilterClientRequest  $filter
     * @return AnonymousResourceCollection
     */
    public function index(RehearsalsFilterClientRequest $filter): AnonymousResourceCollection
    {
        $rehearsals = Rehearsal::filter($filter)->orderBy('id')->get();

        return RehearsalResource::collection($rehearsals);
    }

    /**
     * @param  CreateRehearsalRequest  $request
     * @return RehearsalResource|JsonResponse
     * @throws AuthorizationException
     */
    public function create(CreateRehearsalRequest $request)
    {
        $this->authorize(
            'create',
            [Rehearsal::class, $request->get('band_id')]
        );

        $organization = $request->organization();

        // keeping this check here instead of rehearsal policy
        // because we have to provide a reason, why this action is forbidden
        // if moved to policy, response message will always be the same
        if ($organization->isUserBanned(auth()->id())) {
            return response()->json('you are banned in this organization', Response::HTTP_FORBIDDEN);
        }

        if (! $organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
        )) {
            return response()->json('Selected time is unavailable', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            /** @var Rehearsal $rehearsal */
            $rehearsal = Rehearsal::create($request->getAttributes());
        } catch (PriceCalculationException | InvalidRehearsalDurationException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new RehearsalResource($rehearsal);
    }

    /**
     * @param  RescheduleRehearsalRequest  $request
     * @param  Rehearsal  $rehearsal
     * @return RehearsalResource|JsonResponse
     */
    public function reschedule(RescheduleRehearsalRequest $request, Rehearsal $rehearsal)
    {
        if (! $rehearsal->organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
            $rehearsal
        )) {
            return response()->json('Selected time is unavailable', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $rehearsal->update($request->getRehearsalAttributes());
        } catch (PriceCalculationException | InvalidRehearsalDurationException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new RehearsalResource($rehearsal);
    }

    /**
     * @param  Rehearsal  $rehearsal
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(Rehearsal $rehearsal): JsonResponse
    {
        $this->authorize('delete', $rehearsal);

        if ($rehearsal->isInPast()) {
            return response()->json('you can\'t delete rehearsal in the past', Response::HTTP_FORBIDDEN);
        }

        $rehearsal->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

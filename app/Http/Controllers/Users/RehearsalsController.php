<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\RehearsalsFilterClientRequest;
use App\Http\Requests\Users\CreateRehearsalRequest;
use App\Http\Requests\Users\RescheduleRehearsalRequest;
use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RehearsalsController extends Controller
{
    public function index(RehearsalsFilterClientRequest $filter): AnonymousResourceCollection
    {
        $rehearsalsQuery = Rehearsal::filter($filter)->orderBy('id');

        $rehearsalsQuery->when(auth()->check(), static function (Builder $query) {
            $userId = auth()->id();

            return $query->addSelect(
                [
                    'is_participant' => DB::table('rehearsal_user')
                        ->selectRaw('true::boolean')
                        ->whereRaw('rehearsal_id = rehearsals.id')
                        ->whereRaw("user_id=$userId"),
                ]
            );
        });

        return RehearsalResource::collection($rehearsalsQuery->get());
    }

    public function create(CreateRehearsalRequest $request): RehearsalResource | JsonResponse
    {
        $this->authorize(
            'create',
            [Rehearsal::class, $request->get('band_id')]
        );

        $organization = $request->organization();

        // keeping this check here instead of rehearsal policy
        // because we have to provide a reason, why this action is forbidden
        // if moved to policy, response message will always be the same
        if ($organization->isUserBanned((int) auth()->id())) {
            return response()->json('Вы забанены в этой организации', Response::HTTP_FORBIDDEN);
        }

        if (! $organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
        )) {
            return response()->json('Выбранное время занято', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            /** @var Rehearsal $rehearsal */
            $rehearsal = Rehearsal::create($request->getAttributes());
        } catch (PriceCalculationException | InvalidRehearsalDurationException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new RehearsalResource($rehearsal);
    }

    public function reschedule(
        RescheduleRehearsalRequest $request,
        Rehearsal $rehearsal
    ): RehearsalResource | JsonResponse {
        if (! $rehearsal->organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
            $rehearsal
        )) {
            return response()->json('Выбранное время занято', Response::HTTP_UNPROCESSABLE_ENTITY);
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
            return response()->json("you can't delete rehearsal in the past", Response::HTTP_FORBIDDEN);
        }

        $rehearsal->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

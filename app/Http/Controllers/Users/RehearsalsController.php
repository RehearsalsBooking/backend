<?php declare(strict_types=1);

namespace App\Http\Controllers\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Exceptions\User\TimeIsUnavailableForUsersException;
use App\Exceptions\User\TimeIsUnavailableInRoomException;
use App\Exceptions\User\TooLongRehearsalException;
use App\Exceptions\User\UserHasAnotherRehearsalAtThatTimeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\RehearsalsFilterRequest;
use App\Http\Requests\Users\CreateRehearsalRequest;
use App\Http\Requests\Users\RescheduleRehearsalRequest;
use App\Http\Resources\RehearsalDetailedResource;
use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use App\Models\RehearsalPrice;
use App\Models\RehearsalTimeValidator;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RehearsalsController extends Controller
{
    public function index(RehearsalsFilterRequest $filter): AnonymousResourceCollection
    {
        $rehearsalsQuery = Rehearsal::filter($filter)->orderBy('id');

        /** @phpstan-ignore-next-line  */
        $rehearsalsQuery->when(auth()->check(), static function (Builder $query) {
            $userId = auth()->id();
            /** @phpstan-ignore-next-line  */
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

    /**
     * @throws AuthorizationException
     */
    public function show(Rehearsal $rehearsal): RehearsalDetailedResource
    {
        $this->authorize('seeFullInfo', $rehearsal);

        return new RehearsalDetailedResource($rehearsal);
    }

    /**
     * @throws AuthorizationException
     * @throws InvalidRehearsalDurationException
     * @throws PriceCalculationException
     * @throws TimeIsUnavailableForUsersException
     * @throws UserHasAnotherRehearsalAtThatTimeException
     * @throws TimeIsUnavailableInRoomException
     * @throws TooLongRehearsalException
     */
    public function create(
        CreateRehearsalRequest $request,
        RehearsalTimeValidator $rehearsalTimeValidator
    ): RehearsalResource|JsonResponse {
        $this->authorize(
            'create',
            [Rehearsal::class, $request->roomId(), $request->bandId()]
        );

        $rehearsalTimeValidator->validate($request);

        $rehearsalPrice = new RehearsalPrice(
            $request->roomId(),
            $request->time()->from() ?? throw new PriceCalculationException(),
            $request->time()->to() ?? throw new PriceCalculationException(),
        );
        /** @var Rehearsal $rehearsal */
        $rehearsal = Rehearsal::create(array_merge(
            ['price' => $rehearsalPrice()],
            $request->getAttributes()
        ));

        return new RehearsalResource($rehearsal);
    }

    /**
     * @throws TooLongRehearsalException
     * @throws TimeIsUnavailableForUsersException
     * @throws UserHasAnotherRehearsalAtThatTimeException
     * @throws PriceCalculationException
     * @throws TimeIsUnavailableInRoomException
     * @throws InvalidRehearsalDurationException
     */
    public function reschedule(
        RescheduleRehearsalRequest $request,
        Rehearsal $rehearsal,
        RehearsalTimeValidator $rehearsalTimeValidator
    ): RehearsalResource|JsonResponse {
        $rehearsalTimeValidator->validate($request);

        $rehearsalPrice = new RehearsalPrice(
            $rehearsal->organization_room_id,
            $request->time()->from() ?? throw new PriceCalculationException(),
            $request->time()->to() ?? throw new PriceCalculationException(),
        );
        $rehearsal->update(array_merge(
            ['price' => $rehearsalPrice()],
            $request->getRehearsalAttributes()
        ));

        return new RehearsalResource($rehearsal);
    }

    /**
     * @throws Exception
     */
    public function delete(Rehearsal $rehearsal): JsonResponse
    {
        $this->authorize('manage', $rehearsal);

        if ($rehearsal->isInPast()) {
            return response()->json("you can't delete rehearsal in the past", Response::HTTP_FORBIDDEN);
        }

        $rehearsal->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

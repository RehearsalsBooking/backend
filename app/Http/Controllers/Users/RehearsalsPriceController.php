<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Exceptions\User\TimeIsUnavailableForUsersException;
use App\Exceptions\User\TimeIsUnavailableInRoomException;
use App\Exceptions\User\TooLongRehearsalException;
use App\Exceptions\User\UserHasAnotherRehearsalAtThatTimeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CalculateRehearsalPriceRequest;
use App\Models\RehearsalPrice;
use App\Models\RehearsalTimeValidator;
use Illuminate\Http\JsonResponse;

class RehearsalsPriceController extends Controller
{
    /**
     * @throws InvalidRehearsalDurationException
     * @throws PriceCalculationException
     * @throws TimeIsUnavailableForUsersException
     * @throws TimeIsUnavailableInRoomException
     * @throws UserHasAnotherRehearsalAtThatTimeException
     * @throws TooLongRehearsalException
     */
    public function calculate(
        CalculateRehearsalPriceRequest $request,
        RehearsalTimeValidator $rehearsalTimeValidator
    ): JsonResponse {
        $rehearsalTimeValidator->validate($request);

        $rehearsalPrice = new RehearsalPrice(
            $request->roomId(),
            $request->time()->from() ?? throw new PriceCalculationException(),
            $request->time()->to() ?? throw new PriceCalculationException(),
        );

        return response()->json($rehearsalPrice());
    }
}

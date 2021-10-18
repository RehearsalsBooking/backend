<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\GetOrganizationRoomPriceRequest;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrganizationRoomPricesController extends Controller
{
    /**
     * @param  GetOrganizationRoomPriceRequest  $request
     * @param  OrganizationRoom  $room
     * @return JsonResponse
     */
    public function index(GetOrganizationRoomPriceRequest $request, OrganizationRoom $room): JsonResponse
    {
        if (!$room->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at'),
            $request->getReschedulingRehearsal()
        )) {
            return response()->json(
                'Выбранное время занято.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        try {
            return response()->json($request->getRehearsalPrice());
        } catch (InvalidRehearsalDurationException | PriceCalculationException $e) {
            return response()->json(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}

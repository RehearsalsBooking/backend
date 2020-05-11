<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\GetOrganizationPriceRequest;
use App\Models\Organization\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrganizationPricesController extends Controller
{
    /**
     * @param  GetOrganizationPriceRequest  $request
     * @param  Organization  $organization
     * @return JsonResponse
     */
    public function index(GetOrganizationPriceRequest $request, Organization $organization): JsonResponse
    {
        if (!$organization->isTimeAvailable(
            $request->get('starts_at'),
            $request->get('ends_at')
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
                'Ошибка вычисления стоимости репетиции. Попробуйте выбрать другое время',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

}

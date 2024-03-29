<?php

namespace App\Exceptions\User;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PriceCalculationException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(
            "Не получилось посчитать цену для репетиции",
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}

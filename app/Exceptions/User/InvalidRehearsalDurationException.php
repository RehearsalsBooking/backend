<?php

namespace App\Exceptions\User;

use App\Models\Rehearsal;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InvalidRehearsalDurationException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(
            sprintf(
                "Некорректная длительность репетиции. Время должно быть кратно %s минутам",
                Rehearsal::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES
            ),
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

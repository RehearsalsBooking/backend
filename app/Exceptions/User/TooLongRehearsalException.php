<?php

namespace App\Exceptions\User;

use App\Models\Rehearsal;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TooLongRehearsalException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(
            sprintf(
                "Репетиция должна длиться не более %s минут",
                Rehearsal::MAXIMUM_REHEARSAL_DURATION_IN_MINUTES
            ),
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

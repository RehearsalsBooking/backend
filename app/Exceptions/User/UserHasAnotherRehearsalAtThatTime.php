<?php

namespace App\Exceptions\User;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserHasAnotherRehearsalAtThatTime extends Exception
{

    public function render(): JsonResponse
    {
        return response()->json('У вас имеется другая репетиция в это время', Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

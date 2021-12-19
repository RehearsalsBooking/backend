<?php

namespace App\Exceptions\User;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TimeIsUnavailableInRoomException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(
            'Данное время занято',
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

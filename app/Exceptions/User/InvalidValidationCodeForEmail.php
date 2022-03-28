<?php

namespace App\Exceptions\User;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InvalidValidationCodeForEmail extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json("Неверный код", Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}

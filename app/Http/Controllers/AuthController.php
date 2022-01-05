<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Http\Resources\Users\LoggedUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function me(): LoggedUserResource
    {
        return new LoggedUserResource(auth()->user());
    }

    public function logout(): JsonResponse
    {
        auth('web')->logout();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function test(): JsonResponse|LoggedUserResource
    {
        if (app()->environment('production')) {
            return response()->json([], 404);
        }

        $user = User::firstOrCreate(['email' => 'demo@festic.ru'], ['name' => 'test user']);

        auth('web')->login($user);

        return response()->json(new LoggedUserResource($user), Response::HTTP_OK);
    }

    public function registration(RegistrationRequest $request): JsonResponse
    {
        $newUser = User::create($request->getUserAttributes());

        auth('web')->login($newUser);

        return response()->json(new LoggedUserResource($newUser), Response::HTTP_CREATED);
    }
}

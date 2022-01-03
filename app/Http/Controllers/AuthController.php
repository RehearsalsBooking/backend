<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users\LoggedUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['test']]);
    }

    public function me(): LoggedUserResource
    {
        return new LoggedUserResource(auth()->user());
    }

    public function logout(): JsonResponse
    {
        auth('web')->logout();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function test(Request $request): JsonResponse|LoggedUserResource
    {
        if (app()->environment('production')) {
            return response()->json([], 404);
        }

        $user = User::firstOrCreate(['email' => 'demo@festic.ru'], ['name' => 'test user']);

        auth('web')->login($user);

        $request->session()->regenerate();

        return new LoggedUserResource($user);
    }
}

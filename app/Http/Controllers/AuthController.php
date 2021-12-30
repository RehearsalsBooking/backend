<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['test']]);
    }

    /**
     * Get the authenticated User.
     *
     * @return UserResource
     */
    public function me(): UserResource
    {
        return new UserResource(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        /** @phpstan-ignore-next-line  */
        Auth::guard('sanctum')->logout();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function test(): JsonResponse|UserResource
    {
        if (app()->environment('production')) {
            return response()->json([], 404);
        }

        $user = User::firstOrCreate(['email' => 'test@rehearsals.com'], ['name' => 'test user']);

        /** @phpstan-ignore-next-line  */
        Auth::guard('sanctum')->login($user);

        return new UserResource($user);
    }
}

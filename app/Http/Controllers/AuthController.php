<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users\UserResource;
use App\Models\User;
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
        /** @var User $user */
        $user = auth()->user();
        $user->tokens()->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function test(): JsonResponse
    {
        $user = User::firstOrCreate(['email' => 'test@rehearsals.com'], ['name' => 'test user']);
        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('rehearsals-token')->plainTextToken,
        ]);
    }
}

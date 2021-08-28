<?php

namespace App\Http\Controllers;

use App\Http\Requests\SocialiteLoginRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\UserOAuth;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteLoginController extends Controller
{
    /**
     * @throws Throwable
     */
    public function callback(SocialiteLoginRequest $request): JsonResponse
    {
        $oauthUser = Socialite::driver($request->getProvider())
            ->stateless()
            ->userFromToken($request->getToken());

        $user = UserOAuth::fromSocialiteUser($oauthUser, $request->getProvider());

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('rehearsals-token')->plainTextToken,
        ]);
    }
}

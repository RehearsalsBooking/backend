<?php

namespace App\Http\Controllers;

use App\Http\Requests\SocialiteLoginRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\UserOAuth;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteLoginController extends Controller
{
    /**
     * @throws Throwable
     */
    public function callback(SocialiteLoginRequest $request): JsonResponse
    {
        Log::info('recieved callback');
        Log::info('provider');
        Log::info($request->getProvider());
        Log::info('token');
        Log::info($request->getToken());
        Log::info('fetching user from provider');

        /** @var User $oauthUser */
        $oauthUser = Socialite::driver($request->getProvider())
            ->stateless()
            ->userFromToken($request->getToken());
        Log::info($oauthUser->getEmail());
        Log::info($oauthUser->getId());
        Log::info($oauthUser->getName());
        Log::info($oauthUser->getNickname());
        Log::info($oauthUser->getAvatar());

        $user = UserOAuth::fromSocialiteUser($oauthUser, $request->getProvider());

        Log::info('user to login');
        Log::info($user->toArray());

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('rehearsals-token')->plainTextToken,
        ]);
    }
}

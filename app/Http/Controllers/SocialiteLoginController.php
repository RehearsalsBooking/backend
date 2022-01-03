<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users\LoggedUserResource;
use App\Models\UserOAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteLoginController extends Controller
{
    public function redirect(string $provider): JsonResponse
    {
        return response()->json(
            Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()
        );
    }

    /**
     * @throws Throwable
     */
    public function callback(string $provider): JsonResponse
    {
        $socialiteUser = Socialite::driver($provider)->stateless()->user();

        $user = UserOAuth::fromSocialiteUser($socialiteUser, $provider);

        auth('web')->login($user);

        return response()->json(new LoggedUserResource($user), Response::HTTP_OK);
    }

}

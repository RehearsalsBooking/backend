<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users\UserResource;
use App\Models\UserOAuth;
use Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteLoginController extends Controller
{
    public function redirect(string $provider): string
    {
        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    /**
     * @throws Throwable
     */
    public function callback(string $provider): UserResource
    {
        $socialiteUser = Socialite::driver($provider)->stateless()->user();

        $user = UserOAuth::fromSocialiteUser($socialiteUser, $provider);

        auth('web')->login($user);

        return new UserResource($user);
    }

}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users\LoggedUserResource;
use App\Models\UserOAuth;
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
    public function callback(string $provider): LoggedUserResource
    {
        $socialiteUser = Socialite::driver($provider)->stateless()->user();

        $user = UserOAuth::fromSocialiteUser($socialiteUser, $provider);

        auth('web')->login($user);

        return new LoggedUserResource($user);
    }

}

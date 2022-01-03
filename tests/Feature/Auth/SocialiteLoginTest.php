<?php

namespace Tests\Feature\Auth;

use App\Models\UserOAuth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Tests\TestCase;

class SocialiteLoginTest extends TestCase
{
    /** @test */
    public function it_logins_user_when_he_provides_correct_code(): void
    {
        $clientId = 'client id';
        $user = $this->createUser();

        $socialiteUser = $this->mockSocialiteUser($user->email, $clientId);

        $socialiteProvider = $this->createMock(AbstractProvider::class);
        $socialiteProvider
            ->method('user')
            ->willReturn($socialiteUser);
        $socialiteProvider
            ->method('stateless')
            ->willReturnSelf();

        $stub = $this->createMock(Socialite::class);
        $stub->method('driver')->willReturn($socialiteProvider);

        $this->app->instance(Socialite::class, $stub);

        UserOAuth::create([
            'social_id' => $clientId,
            'social_type' => 'google',
            'user_id' => $user->id,
        ]);

        $this->assertGuest('web');

        $response = $this->getJson(route('socialite.callback', 'google'));
        $response->assertOk();
        $this->assertEquals($user->id, $response->json('id'));

        $this->assertAuthenticatedAs($user, 'web');
    }

    /** @test */
    public function it_returns_redirect_link(): void
    {
        $redirectLink = 'some oauth link';

        $redirectResponse = $this->createMock(RedirectResponse::class);
        $redirectResponse
            ->method('getTargetUrl')
            ->willReturn($redirectLink);

        $socialiteProvider = $this->createMock(AbstractProvider::class);
        $socialiteProvider
            ->method('redirect')
            ->willReturn($redirectResponse);
        $socialiteProvider
            ->method('stateless')
            ->willReturnSelf();

        $stub = $this->createMock(Socialite::class);
        $stub->method('driver')->willReturn($socialiteProvider);

        $this->app->instance(Socialite::class, $stub);

        $response = $this->json('get', route('socialite.redirect', 'google'));
        $response->assertOk();
        $this->assertEquals($redirectLink, $response->json());
    }

}

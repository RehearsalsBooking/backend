<?php

namespace Tests\Feature\Auth;

use App\Models\User as LaravelUser;
use App\Models\UserOAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User;
use SocialiteProviders\VKontakte\Provider;
use Tests\TestCase;

class SocialiteLoginTest extends TestCase
{
    use RefreshDatabase;

    private string $method = 'post';

    private function mockSocialiteForGoogle(
        string $email = 'foo@bar.com',
        string $token = 'token',
        string $id = 'client_id'
    ): void {
        $socialiteUser = $this->createMock(User::class);
        $socialiteUser
            ->method('getName')
            ->willReturn('name');
        $socialiteUser
            ->method('getEmail')
            ->willReturn($email);
        $socialiteUser
            ->method('getId')
            ->willReturn($id);

        $googleProvider = $this->createMock(GoogleProvider::class);
        $googleProvider
            ->method('userFromToken')
            ->willReturn($socialiteUser);
        $googleProvider
            ->method('stateless')
            ->willReturn($googleProvider);

        $stub = $this->createMock(Socialite::class);
        $stub->method('driver')->with('google')->willReturn($googleProvider);

        $this->app->instance(Socialite::class, $stub);
    }

    private function mockSocialiteForVK(
        string $email = 'foo@bar.com',
        string $token = 'token',
        string $id = 'client_id'
    ): void {
        $socialiteUser = $this->createMock(User::class);
        $socialiteUser
            ->method('getName')
            ->willReturn('name');
        $socialiteUser
            ->method('getEmail')
            ->willReturn($email);
        $socialiteUser
            ->method('getId')
            ->willReturn($id);

        $vkProvider = $this->createMock(Provider::class);
        $vkProvider
            ->method('userFromToken')
            ->willReturn($socialiteUser);
        $vkProvider
            ->method('stateless')
            ->willReturn($vkProvider);


        $stub = $this->createMock(Socialite::class);
        $stub->method('driver')->with('vkontakte')->willReturn($vkProvider);

        $this->app->instance(Socialite::class, $stub);
    }

    /** @test */
    public function it_logins_user_when_he_provides_correct_token(): void
    {
        $user = $this->createUser();

        $clientId = 'client id';
        UserOAuth::create([
            'social_id' => $clientId,
            'social_type' => 'google',
            'user_id' => $user->id,
        ]);
        $this->mockSocialiteForGoogle(email: $user->email, id: $clientId);

        $response = $this->json($this->method, route('socialite.login', 'google'), [
            'token' => 'some valid token',
            'provider' => 'google'
        ]);
        $response->assertOk();
        $this->assertEquals($user->id, $response->json('user.id'));

        $token = $response->json('token');

        $response = $this->get(route('me'), ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        $this->assertEquals($user->id, $response->json('data.id'));
    }

    /** @test */
    public function it_logins_user_when_he_provides_correct_token_from_another_oauth(): void
    {
        $user = $this->createUser();

        $clientId = 'client id';
        UserOAuth::create([
            'social_id' => $clientId,
            'social_type' => 'vkontakte',
            'user_id' => $user->id,
        ]);
        $this->mockSocialiteForGoogle(email: $user->email, id: $clientId);

        $response = $this->json($this->method, route('socialite.login', 'google'), [
            'token' => 'some valid token',
            'provider' => 'google'
        ]);
        $response->assertOk();

        $this->assertEquals(1, LaravelUser::count());
        $this->assertEquals($user->id, $response->json('user.id'));

        $token = $response->json('token');

        $response = $this->get(route('me'), ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        $this->assertEquals($user->id, $response->json('data.id'));
    }

    /** @test */
    public function it_creates_user_who_has_not_logged_in_with_socialite_yet(): void
    {
        $clientId = 'client id';
        $userEmail = 'some@email.com';

        $this->assertEquals(0, LaravelUser::count());
        $this->assertEquals(0, UserOAuth::count());
        $this->mockSocialiteForVK(email: $userEmail, id: $clientId);

        $response = $this->json($this->method, route('socialite.login', 'vkontakte'), [
            'token' => 'some valid token',
            'provider' => 'vkontakte',
            'email' => $userEmail
        ]);
        $response->assertOk();

        $this->assertEquals(1, LaravelUser::count());
        $this->assertEquals(1, UserOAuth::count());
        $createdUser = LaravelUser::first();
        $this->assertEquals($createdUser->email, $userEmail);
        $this->assertEquals($createdUser->id, $response->json('user.id'));

        $token = $response->json('token');

        $response = $this->get(route('me'), ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        $this->assertEquals($createdUser->id, $response->json('data.id'));
    }

    /** @test */
    public function it_requires_email_for_vkontakte_provider(): void
    {
        $this->mockSocialiteForVK();
        $response = $this->json($this->method, route('socialite.login', 'google'), [
            'token' => 'some valid token',
            'provider' => 'vkontakte'
        ]);
        $response->assertJsonValidationErrors('email');
    }
}

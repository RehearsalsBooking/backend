<?php

namespace Tests\Feature\Auth;

use App\Models\UserOAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User;
use Tests\TestCase;

class SocialiteLoginTest extends TestCase
{
    use RefreshDatabase;

    private string $method = 'post';

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
        $this->mockSocialite(email: $user->email, id: $clientId);

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

    private function mockSocialite(
        string $email = 'foo@bar.com',
        string $token = 'token',
        string $id = 'client_id'
    ): void {
        $socialiteUser = $this->createMock(User::class);
        $socialiteUser->expects($this->any())
            ->method('getName')
            ->willReturn('name');
        $socialiteUser->expects($this->any())
            ->method('getEmail')
            ->willReturn($email);
        $socialiteUser->expects($this->any())
            ->method('getId')
            ->willReturn($id);

        $provider = $this->createMock(GoogleProvider::class);
        $provider->expects($this->any())
            ->method('userFromToken')
            ->willReturn($socialiteUser);
        $provider->expects($this->any())
            ->method('stateless')
            ->willReturn($provider);

        $stub = $this->createMock(Socialite::class);
        $stub->expects($this->any())
            ->method('driver')
            ->willReturn($provider);

        // Replace Socialite Instance with our mock
        $this->app->instance(Socialite::class, $stub);
    }

}

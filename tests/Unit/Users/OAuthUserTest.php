<?php

namespace Tests\Unit\Users;

use App\Models\User;
use App\Models\UserOAuth;
use Tests\TestCase;

class OAuthUserTest extends TestCase
{
    /** @test */
    public function it_fetches_correct_user_who_has_oauth_authorization(): void
    {
        $user = $this->createUser();

        $clientId = 'client_id';
        $provider = 'google';

        UserOAuth::create([
            'social_id' => $clientId,
            'social_type' => $provider,
            'user_id' => $user->id,
        ]);

        $socialiteUser = $this->mockSocialiteUser($user->email, $clientId);

        $userFromSocialite = UserOAuth::fromSocialiteUser($socialiteUser, $provider);
        $this->assertEquals($user->id, $userFromSocialite->id);
    }

    /** @test */
    public function it_creates_user_and_oauth_user_for_newly_registered_user(): void
    {
        $clientId = 'client_id';
        $provider = 'google';
        $email = 'user@email.com';

        $socialiteUser = $this->mockSocialiteUser($email, $clientId);

        $this->assertDatabaseCount(UserOAuth::class, 0);
        $this->assertDatabaseCount(User::class, 0);

        $userFromSocialite = UserOAuth::fromSocialiteUser($socialiteUser, $provider);

        $this->assertDatabaseCount(UserOAuth::class, 1);
        $this->assertDatabaseCount(User::class, 1);

        $this->assertEquals($email, $userFromSocialite->email);
        $this->assertDatabaseHas(UserOAuth::class, [
            'social_type' => $provider,
            'social_id' => $clientId,
            'user_id' => $userFromSocialite->id
        ]);
    }

    /** @test */
    public function it_creates_user_without_provided_email(): void
    {
        $clientId = 'client_id';
        $provider = 'google';

        $socialiteUser = $this->mockSocialiteUser(null, $clientId);

        $this->assertDatabaseCount(UserOAuth::class, 0);
        $this->assertDatabaseCount(User::class, 0);

        $userFromSocialite = UserOAuth::fromSocialiteUser($socialiteUser, $provider);

        $this->assertDatabaseCount(UserOAuth::class, 1);
        $this->assertDatabaseCount(User::class, 1);

        $this->assertNull($userFromSocialite->email);
        $this->assertDatabaseHas(UserOAuth::class, [
            'social_type' => $provider,
            'social_id' => $clientId,
            'user_id' => $userFromSocialite->id
        ]);
    }

    /** @test */
    public function it_fetches_correct_user_who_has_oauth_authorization_from_another_provider_and_creates_another_oauth_user(
    ): void
    {
        $user = $this->createUser();

        $clientId = 'vkontakte_id';

        UserOAuth::create([
            'social_id' => 'google_id',
            'social_type' => 'google',
            'user_id' => $user->id,
        ]);

        $socialiteUser = $this->mockSocialiteUser($user->email, $clientId);

        $newProvider = 'vkontakte';

        $userFromSocialite = UserOAuth::fromSocialiteUser($socialiteUser, $newProvider);
        $this->assertEquals($user->id, $userFromSocialite->id);

        $this->assertEquals(2, UserOAuth::count());
        $this->assertDatabaseHas(UserOAuth::class, [
            'social_type' => $newProvider,
            'social_id' => $clientId,
            'user_id' => $user->id
        ]);
    }
}

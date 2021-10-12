<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{

    private User $user;

    private array $credentials = [
        'email' => 'some@email.com',
        'password' => 'some password',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser([
            'email' => $this->credentials['email'],
        ]);
    }

    /** @test */
    public function logged_in_user_can_fetch_info_about_himself(): void
    {
        $this->json('get', route('me'))->assertUnauthorized();

        $this->actingAs($this->user);
        $response = $this->get(route('me'));

        $response->assertOk();

        $this->assertEquals(
            (new UserResource($this->user))->toResponse(null)->getData(true)['data'],
            $response->json('data')
        );
    }

    /** @test */
    public function user_can_logout(): void
    {
        $this->json('post', route('logout'))->assertUnauthorized();

        $token = $this->user->createToken('some token')->plainTextToken;

        $response = $this->json('post', route('logout'), [], ['Authorization' => 'Bearer '.$token]);

        $response->assertNoContent();

        $this->assertEquals(0, $this->user->tokens()->count());
    }

    /** @test */
    public function it_logins_test_user(): void
    {
        $response = $this->json('post', route('login.test'));
        $response->assertOk();
        $token = $response->json('token');

        $response = $this->get(route('me'), ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        $testUser = User::where('email', 'test@rehearsals.com')->first();

        $this->assertEquals(
            (new UserResource($testUser))->toResponse(null)->getData(true)['data'],
            $response->json('data')
        );
        $this->assertEquals($testUser->id, $response->json('data.id'));
    }
}

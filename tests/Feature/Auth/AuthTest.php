<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

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
    public function it_doesnt_login_user_with_invalid_credentials(): void
    {
        $this->post(route('login'), [
            'email' => $this->credentials['email'],
            'password' => 'some wrong password',
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->withoutExceptionHandling();
        $this->post(route('login'), [
            'email' => 'unknown@email.com',
            'password' => $this->credentials['password'],
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertGuest();
    }

    /** @test */
    public function logged_in_user_can_fetch_info_about_himself(): void
    {
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
        $token = $this->user->createToken('some token')->plainTextToken;

        $response = $this->json('post', route('logout'), [], ['Authorization' => 'Bearer '.$token]);

        $response->assertNoContent();

        $this->assertEquals(0, $this->user->tokens()->count());
    }
}

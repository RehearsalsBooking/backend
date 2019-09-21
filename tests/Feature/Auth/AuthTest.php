<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;

    private $credentials = [
        'email' => 'some@email.com',
        'password' => 'somepassword'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'password' => bcrypt($this->credentials['password']),
            'email' => $this->credentials['email']
        ]);
    }

    /** @test */
    public function it_logins_users(): void
    {
        $response = $this->post(route('login'), $this->credentials);

        $response->assertOk();

        $data = $response->json();

        $this->assertEquals(
            auth()->setToken($data['access_token'])->user()->id,
            $this->user->id
        );
    }

    /** @test */
    public function it_doesnt_login_user_with_invalid_credetntials(): void
    {
        $this->post(route('login'), [
            'email' => $this->credentials['email'],
            'password' => 'some wrong password'
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->post(route('login'), [
            'email' => 'unknown@email.com',
            'password' => $this->credentials['password']
        ])->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_with_valid_token_can_fetch_info_about_himself(): void
    {
        $token = $this->getTokenForUser();

        $response = $this->get(route('me'), $this->getAuthHeader($token));

        $response->assertOk();

        $this->assertEquals(
            (new UserResource($this->user))->toArray(null),
            $response->json('data')
        );
    }

    /** @test */
    public function user_with_invalid_token_cannot_fetch_info_about_himself(): void
    {
        $this->json('get', route('me'), $this->getAuthHeader('some invalid token'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_with_valid_token_can_logout(): void
    {
        $token = $this->getTokenForUser();

        $authHeader = $this->getAuthHeader($token);

        $response = $this->json('post', route('logout'), $authHeader);

        $response->assertOk();

        $this->json(
            'get',
            route('me'),
            $authHeader
        )->assertStatus(Response::HTTP_UNAUTHORIZED);

    }

    /**
     * @return string
     */
    protected function getTokenForUser(): string
    {
        return $this->post(route('login'), $this->credentials)->json('access_token');
    }

    /**
     * @param string $token
     * @return array
     */
    protected function getAuthHeader(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}"
        ];
    }
}

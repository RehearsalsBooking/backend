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

    private User $user;

    private array $credentials = [
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
    public function it_logins_users_with_correct_credentials(): void
    {
        $response = $this->post(route('login'), $this->credentials);

        $response->assertOk();
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
    public function logged_in_user_can_fetch_info_about_himself(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('me'));

        $response->assertOk();

        $this->assertEquals(
            (new UserResource($this->user))->toArray(null),
            $response->json('data')
        );
    }
}

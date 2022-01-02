<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Users\LoggedUserResource;
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
            (new LoggedUserResource($this->user))->toResponse(null)->getData(true)['data'],
            $response->json('data')
        );
    }

    /** @test */
    public function it_doesnt_login_as_test_user_in_production_environment(): void
    {
        app()->detectEnvironment(function () {
            return 'production';
        });
        $this->assertEquals('production', app()->environment());
        $this->json('post', route('login.test'))->assertNotFound();
    }
}

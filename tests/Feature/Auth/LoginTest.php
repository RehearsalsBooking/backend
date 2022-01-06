<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Users\LoggedUserResource;
use App\Models\User;
use Hash;
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class LoginTest extends TestCase
{
    private array $credentials = [
        'email' => 'some@email.com',
        'password' => 'some password',
    ];
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser([
            'email' => $this->credentials['email'],
            'password' => Hash::make($this->credentials['password'])
        ]);
    }

    /** @test */
    public function it_logins_user(): void
    {
        $this->assertGuest();
        $response = $this->json('post', route('login'), $this->credentials);
        $response->assertOk();

        $this->assertEquals(
            (new LoggedUserResource($this->user))->response()->getData(true)['data'],
            $response->json()
        );
        $this->assertAuthenticatedAs($this->user, 'web');
    }

    /** @test */
    public function it_throttles_login_requests(): void
    {
        $this->withMiddleware(ThrottleRequests::class);
        $loginAttemptsAllowed = 5;

        foreach (range(1, $loginAttemptsAllowed) as $_) {
            $this->json('post', route('login'), [])->assertUnprocessable();
        }
        $this->json('post', route('login'), [])->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * @test
     * @dataProvider invalidLoginData
     */
    public function it_validates_login_data($data, $error): void
    {
        $response = $this->json('post', route('login'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error);
        $this->assertGuest('web');
    }

    public function invalidLoginData()
    {
        return [
            [
                [
                    'email' => 'some@email.com',
                ],
                'password'
            ],

            [
                [
                    'password' => 'some password',
                ],
                'email'
            ],

            [
                [
                    'email' => 'some@email.com',
                    'password_confirmation' => 'some password'
                ],
                'password'
            ],

            [
                [
                    'email' => 'some@email.com',
                    'password' => 'incorrect password',
                ],
                'login'
            ],
        ];
    }
}

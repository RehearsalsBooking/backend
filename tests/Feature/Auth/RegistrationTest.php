<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Users\LoggedUserResource;
use App\Models\EmailVerification;
use App\Models\User;
use Hash;
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    private array $credentials = [
        'name' => 'new user',
        'email' => 'some@email.com',
        'password' => 'some password',
        'password_confirmation' => 'some password'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->credentials['code'] = EmailVerification::createCodeForEmail($this->credentials['email']);
    }

    /** @test */
    public function it_registers_user(): void
    {
        $this->assertDatabaseCount(User::class, 0);
        $this->assertDatabaseCount(EmailVerification::class, 1);
        $this->assertGuest();
        $response = $this->json('post', route('registration'), $this->credentials);
        $response->assertCreated();

        $this->assertDatabaseCount(User::class, 1);
        $this->assertDatabaseHas(User::class, []);
        $registeredUser = User::first();
        $this->assertEquals(
            (new LoggedUserResource($registeredUser))->response()->getData(true)['data'],
            $response->json()
        );
        $this->assertEquals($this->credentials['email'], $registeredUser->email);
        $this->assertEquals($this->credentials['name'], $registeredUser->name);
        $this->assertTrue(Hash::check($this->credentials['password'], $registeredUser->password));
        $this->assertDatabaseCount(EmailVerification::class, 0);
        $this->assertAuthenticatedAs($registeredUser);
    }

    /** @test */
    public function it_validates_user_email(): void
    {
        $this->assertDatabaseCount(User::class, 0);
        $this->assertGuest();
        $data = $this->credentials;
        $data['code'] = 'some incorrect code';
        $this->assertNotEquals($data['code'], $this->credentials['code']);
        $this->json('post', route('registration'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');

        $this->assertDatabaseCount(User::class, 0);
    }

    /** @test */
    public function it_throttles_registration_requests(): void
    {
        $this->withMiddleware(ThrottleRequests::class);
        $loginAttemptsAllowed = 3;

        foreach (range(1, $loginAttemptsAllowed) as $_) {
            $this->json('post', route('registration'), [])->assertUnprocessable();
        }
        $this->json('post', route('registration'), [])->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /** @test */
    public function it_validates_uniqueness_of_email(): void
    {
        $this->createUser(['email' => $this->credentials['email']]);
        $response = $this->json('post', route('registration'), $this->credentials);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * @test
     * @dataProvider invalidRegistrationData
     */
    public function it_validates_registration_data($data, $error): void
    {
        $response = $this->json('post', route('registration'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error);
    }

    public function invalidRegistrationData()
    {
        return [
            [
                [
                    'email' => 'some@email.com',
                    'password' => 'some password',
                    'password_confirmation' => 'some password',
                    'code' => 'some code'
                ],
                'name'
            ],

            [
                [
                    'name' => 'new user',
                    'password' => 'some password',
                    'password_confirmation' => 'some password',
                    'code' => 'some code'
                ],
                'email'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password_confirmation' => 'some password',
                    'code' => 'some code'
                ],
                'password'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password' => 'some password',
                    'code' => 'some code'
                ],
                'password'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password' => 'some password',
                    'password_confirmation' => 'incorrect confirmation',
                    'code' => 'some code'
                ],
                'password'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password' => 'short',
                    'password_confirmation' => 'short',
                    'code' => 'some code'
                ],
                'password'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password' => 'some password',
                    'password_confirmation' => 'some password',
                ],
                'code'
            ],
        ];
    }
}

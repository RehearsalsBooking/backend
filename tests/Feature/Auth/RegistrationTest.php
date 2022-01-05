<?php

namespace Tests\Feature\Auth;

use App\Http\Resources\Users\LoggedUserResource;
use App\Models\User;
use Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    private array $credentials = [
        'name' => 'new user',
        'email' => 'some@email.com',
        'password' => 'some password',
        'password_confirmation' => 'some password'
    ];

    /** @test */
    public function it_registers_user(): void
    {
        $this->assertDatabaseCount(User::class, 0);
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
        $this->assertAuthenticatedAs($registeredUser);
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
                    'password_confirmation' => 'some password'
                ],
                'name'
            ],

            [
                [
                    'name' => 'new user',
                    'password' => 'some password',
                    'password_confirmation' => 'some password'
                ],
                'email'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password_confirmation' => 'some password'
                ],
                'password'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password' => 'some password',
                ],
                'password'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password' => 'some password',
                    'password_confirmation' => 'incorrect confirmation',
                ],
                'password'
            ],

            [
                [
                    'name' => 'new user',
                    'email' => 'some@email.com',
                    'password' => 'short',
                    'password_confirmation' => 'short',
                ],
                'password'
            ],
        ];
    }
}

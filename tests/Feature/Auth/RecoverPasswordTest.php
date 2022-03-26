<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RecoverPasswordTest extends TestCase
{
    private string $route;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->route = route('password.recover');
    }

    /**
     * @test
     * @dataProvider invalidRecoverPasswordData
     */
    public function it_validates_password_recovery_request($data, $error): void
    {
        $this->json('post', $this->route, $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($error);
    }

    /** @test */
    public function it_validates_that_email_registered(): void
    {
        $invalidCredentials = ['email' => 'some-invalid@email.com'];
        $this->assertDatabaseMissing(User::class, $invalidCredentials);
        $this->json('post', $this->route, array_merge(
            $invalidCredentials,
            [
                'token' => 'token',
                'password' => 'some new password',
                'password_confirmation' => 'some new password',
            ]
        ))
            ->assertUnprocessable();
    }

    /** @test */
    public function it_validates_that_token_is_valid(): void
    {
        $user = $this->createUser();
        $this->createResetTokenForUser($user);
        $this->json('post', $this->route, array_merge(
            [
                'email' => $user->email,
                'token' => 'some invalid token',
                'password' => 'some new password',
                'password_confirmation' => 'some new password',
            ]
        ))
            ->assertUnprocessable();
    }

    /** @test */
    public function it_resets_password(): void
    {
        $user = $this->createUser();
        $token = $this->createResetTokenForUser($user);
        $newPassword = 'some new password';
        $this->assertFalse(Hash::check($newPassword, $user->getAuthPassword()));
        $this->json('post', $this->route, array_merge(
            [
                'email' => $user->email,
                'token' => $token,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]
        ))
            ->assertOk();
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->getAuthPassword()));
    }

    public function invalidRecoverPasswordData(): array
    {
        return [
            [
                [
                    'token' => 'token',
                    'password' => 'some new password',
                    'password_confirmation' => 'some new password',
                ],
                'email'
            ],

            [
                [
                    'token' => 'token',
                    'email' => 'some@email.com',
                    'password' => 'some new password',
                    'password_confirmation' => 'wrong confirmation',
                ],
                'password'
            ],

            [
                [
                    'token' => 'token',
                    'email' => 'some@email.com',
                    'password_confirmation' => 'some new password',
                ],
                'password'
            ],

            [
                [
                    'token' => 'token',
                    'email' => 'some@email.com',
                    'password' => 'short',
                    'password_confirmation' => 'short',
                ],
                'password'
            ],

            [
                [
                    'email' => 'some@email.com',
                    'password' => 'some new password',
                    'password_confirmation' => 'some new password',
                ],
                'token'
            ],
        ];
    }

    private function createResetTokenForUser(CanResetPassword $user): string
    {
        $key = config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $repository = app()->make(DatabaseTokenRepository::class,
            ['table' => 'password_resets', 'hashKey' => $key]);

        return $repository->create($user);
    }
}

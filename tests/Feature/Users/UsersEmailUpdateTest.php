<?php

namespace Tests\Feature\Users;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UsersEmailUpdateTest extends TestCase
{
    private User $user;
    private string $endpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->endpoint = route('users.update.email');
        Mail::fake();
    }

    /** @test */
    public function user_can_change_his_email(): void
    {
        $newEmail = 'new@email.com';
        $code = EmailVerification::createCodeForEmail($newEmail);

        $this->actingAs($this->user);

        $response = $this->json('put', $this->endpoint, [
            'email' => $newEmail,
            'code' => $code
        ]);
        $response->assertOk();

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'email' => $newEmail]);
        $this->assertDatabaseCount(EmailVerification::class, 0);
    }

    /** @test */
    public function unauthorized_user_cannot_update_email(): void
    {
        $this->json('put', $this->endpoint)->assertUnauthorized();
    }

    /** @test
     * @dataProvider invalidUserData
     */
    public function it_responses_with_422_when_user_provided_invalid_data($invalidData, $errorKey): void
    {
        $this->actingAs($this->user);

        $this->json('put', $this->endpoint, $invalidData)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorKey);
    }

    public function invalidUserData(): array
    {
        return [
            [
                [
                    'code' => 'some code'
                ],
                'email',
            ],
            [
                [
                    'email' => 'some@mail.com'
                ],
                'code',
            ],
            [
                [
                    'email' => 'incorrect email',
                    'code' => 'some code'
                ],
                'email',
            ],
            [
                [
                    'email' => 'new@mail.com',
                    'code' => 'incorrect code'
                ],
                'code',
            ],
        ];
    }
}

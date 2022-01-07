<?php

namespace Tests\Feature\Auth;

use App\Mail\EmailVerificationCode;
use App\Models\EmailVerification;
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    protected string $endpoint;
    protected string $method;
    private string $email = 'some@email.com';

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->endpoint = route('email.verification');
        $this->method = 'post';
    }

    /** @test */
    public function it_sends_email_verification_code(): void
    {
        $this->assertDatabaseCount(EmailVerification::class, 0);

        $response = $this->json($this->method, $this->endpoint, ['email' => $this->email]);
        $response->assertCreated();

        $this->assertDatabaseCount(EmailVerification::class, 1);
        $this->assertDatabaseHas(EmailVerification::class, ['email' => $this->email]);

        $code = EmailVerification::first()->code;
        Mail::assertSent(EmailVerificationCode::class, function (EmailVerificationCode $mail) use ($code) {
            return $mail->hasTo($this->email) && $mail->code === $code;
        });
    }

    /** @test */
    public function it_stores_only_one_code(): void
    {
        $this->assertDatabaseCount(EmailVerification::class, 0);

        $response = $this->json($this->method, $this->endpoint, ['email' => $this->email]);
        $response->assertCreated();

        $this->assertDatabaseCount(EmailVerification::class, 1);
        $this->assertDatabaseHas(EmailVerification::class, ['email' => $this->email]);

        $code = EmailVerification::first()->code;
        Mail::assertSent(EmailVerificationCode::class, function (EmailVerificationCode $mail) use ($code) {
            return $mail->hasTo($this->email) && $mail->code === $code;
        });

        $response = $this->json($this->method, $this->endpoint, ['email' => $this->email]);
        $response->assertCreated();

        $this->assertDatabaseCount(EmailVerification::class, 1);
        $this->assertDatabaseHas(EmailVerification::class, ['email' => $this->email]);

        $newCode = EmailVerification::first()->code;
        $this->assertNotEquals($code, $newCode);
        Mail::assertSent(EmailVerificationCode::class, function (EmailVerificationCode $mail) use ($newCode) {
            return $mail->hasTo($this->email) && $mail->code === $newCode;
        });
    }

    /**
     * @test
     * @dataProvider getInvalidEmails
     */
    public function it_validates_email($data): void
    {
        $this->json($this->method, $this->endpoint, $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function it_throttles_requests(): void
    {
        $this->withMiddleware(ThrottleRequests::class);
        $loginAttemptsAllowed = 3;

        foreach (range(1, $loginAttemptsAllowed) as $_) {
            $this->json($this->method, $this->endpoint, [])->assertUnprocessable();
        }
        $this->json($this->method, $this->endpoint, [])->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    public function getInvalidEmails(): array
    {
        return [
            [
                ['email' => 'incorrect email']
            ],
            [
                ['email' => null]
            ]
        ];
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Mail\EmailVerificationCode;
use App\Models\EmailVerification;
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    protected string $endpoint;
    protected string $method;
    private string $email = 'some@email.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->endpoint = route('email.verification');
        $this->method = 'post';
    }

    /** @test */
    public function it_sends_email_verification_code(): void
    {
        Mail::fake();

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
    public function mail_contains_code(): void
    {
        $code = Str::random(5);
        $mail = new EmailVerificationCode($code);
        $mail->assertSeeInHtml($code);
    }

    /** @test */
    public function it_stores_only_one_code(): void
    {
        Mail::fake();

        $this->assertDatabaseCount(EmailVerification::class, 0);

        $response = $this->json($this->method, $this->endpoint, ['email' => $this->email]);
        $response->assertCreated();

        $this->assertDatabaseCount(EmailVerification::class, 1);
        $this->assertDatabaseHas(EmailVerification::class, ['email' => $this->email]);

        $code = EmailVerification::first()->code;
        Mail::assertSent(EmailVerificationCode::class, function (EmailVerificationCode $mail) use ($code) {
            return $mail->hasTo($this->email) && $mail->code === $code;
        });

        $this->travel(EmailVerification::EXPIRATION_MINUTES + 10)->minutes();

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

    /** @test */
    public function it_doesnt_send_another_code_if_previous_is_not_expired(): void
    {
        Mail::fake();

        EmailVerification::create(['email' => $this->email, 'code' => 'some code']);

        $code = EmailVerification::first()->code;
        $response = $this->json($this->method, $this->endpoint, ['email' => $this->email]);
        $response->assertCreated();

        $newCode = EmailVerification::first()->code;
        $this->assertEquals($code, $newCode);
        Mail::assertNothingSent();
    }

    /**
     * @test
     * @dataProvider getInvalidEmails
     */
    public function it_validates_email($data): void
    {
        Mail::fake();

        $this->json($this->method, $this->endpoint, $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        Mail::assertNothingSent();
    }

    /** @test */
    public function it_doesnt_send_code_if_user_with_given_email_already_registered(): void
    {
        Mail::fake();

        $user = $this->createUser();
        $this->json($this->method, $this->endpoint, ['email' => $user->email])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        Mail::assertNothingSent();
    }

    /** @test */
    public function it_throttles_requests(): void
    {
        Mail::fake();

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

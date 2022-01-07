<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendPasswordRecoverLinkTest extends TestCase
{
    protected $route;
    private array $credentials = [
        'email' => 'some@email.com',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->route = route('password.send-recovery-link');
    }

    /** @test */
    public function it_validates_that_user_registered(): void
    {
        Notification::fake();
        $invalidCredentials = ['email' => 'some-invalid@email.com'];
        $this->assertDatabaseMissing(User::class, $invalidCredentials);
        $this->json('post', $this->route, $invalidCredentials)
            ->assertUnprocessable();
        Notification::assertNothingSent();
    }

    /** @test */
    public function it_doesnt_send_link_if_another_link_was_sent_recently(): void
    {
        Notification::fake();
        $this->createUser($this->credentials);
        $this->json('post', $this->route, $this->credentials);
        $this->travel(5)->minutes();
        $this->json('post', $this->route, $this->credentials)->assertUnprocessable();
        Notification::assertSentTimes(ResetPassword::class, 1);
    }

    /** @test */
    public function it_sends_password_reset_link_to_users_email(): void
    {
        Notification::fake();
        $user = $this->createUser($this->credentials);
        $response = $this->json('post', $this->route, $this->credentials);
        $response->assertCreated();
        Notification::assertSentTo($user, ResetPassword::class);
    }
}

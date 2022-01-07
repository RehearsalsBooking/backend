<?php

namespace Tests\Feature\Invites;

use App\Mail\NewInvite;
use App\Models\Band;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Queue;
use Mail;
use Tests\TestCase;

class BandsInviteTest extends TestCase
{
    private User $bandAdmin;
    private Band $band;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bandAdmin = $this->createUser();
        $this->band = $this->createBandForUser($this->bandAdmin);
    }

    /** @test */
    public function admin_of_band_can_invite_registered_users_to_his_band(): void
    {
        Mail::fake();

        $this->actingAs($this->bandAdmin);

        $invitedUser = $this->createUser();
        $roles = ['guitarist', 'vocal'];

        $this->assertEquals(0, $this->band->invites()->count());
        $this->assertEquals(0, $invitedUser->invites()->count());

        $response = $this->json(
            'post',
            route('bands.invites.create', [$this->band]),
            [
                'band_id' => $this->band->id,
                'email' => $invitedUser->email,
                'roles' => $roles,
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, $this->band->invites()->count());
        $this->assertEquals(1, $invitedUser->invites()->count());
        $this->assertEquals(
            $invitedUser->invites->pluck('id'),
            $this->band->fresh()->invites->pluck('id')
        );
        $this->assertEquals($roles, $this->band->fresh()->invites->first()->roles);

        Mail::assertQueued(NewInvite::class, function (NewInvite $mail) use ($invitedUser) {
            return $mail->hasTo($invitedUser->email) && $mail->band->id === $this->band->id;
        });
    }

    /** @test */
    public function admin_of_band_can_invite_unregistered_users_to_his_band(): void
    {
        Mail::fake();

        $this->actingAs($this->bandAdmin);

        $invitedUserEmail = 'some@mail.com';
        $role = ['guitarist'];

        $this->assertEquals(0, $this->band->invites()->count());

        $response = $this->json(
            'post',
            route('bands.invites.create', [$this->band]),
            [
                'band_id' => $this->band->id,
                'email' => $invitedUserEmail,
                'roles' => $role,
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, $this->band->invites()->count());
        $this->assertEquals(
            $response->json('id'),
            $this->band->fresh()->invites->first()->id
        );
        $this->assertEquals($role, $this->band->fresh()->invites->first()->roles);
        Mail::assertQueued(NewInvite::class, function (NewInvite $mail) use ($invitedUserEmail) {
            return $mail->hasTo($invitedUserEmail) && $mail->band->id === $this->band->id;
        });
    }
    
    /** @test */
    public function mail_contains_band(): void
    {
        $mail = new NewInvite($this->band);
        $mail->assertSeeInHtml($this->band->name);
    }

    /** @test */
    public function band_admin_can_cancel_member_invite(): void
    {
        Mail::fake();

        $invitedUser = $this->createUser();

        $invite = $this->band->invite($invitedUser->email);

        $this->assertEquals(1, $this->band->invites()->count());
        $this->assertEquals(1, $invitedUser->invites()->count());

        $this->actingAs($this->bandAdmin);

        $response = $this->json(
            'delete',
            route('bands.invites.delete', [$this->band, $invite->id])
        );

        $response->assertOk();

        $this->assertEquals(0, $this->band->invites()->count());
        $this->assertEquals(0, $invitedUser->invites()->count());
    }
}

<?php

namespace Tests\Feature\Invites;

use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class AcceptInviteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthorized_user_cannot_accept_invite_to_band(): void
    {
        $this
            ->json('post', route('invites.accept', 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_can_accept_only_his_invite_to_band(): void
    {
        $max = $this->createUser();
        $band = $this->createBand();
        $invite = $this->createInvite([
            'user_id' => $max->id,
            'band_id' => $band->id,
        ]);

        $john = $this->createUser();
        $this->actingAs($john);

        $this
            ->json('post', route('invites.accept', $invite->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEquals(1, Invite::count());
        $this->assertDatabaseHas('band_user_invites', ['user_id' => $max->id]);
        $this->assertEquals(0, $max->bands()->count());
        $this->assertEquals(0, $john->bands()->count());
        $this->assertEquals(0, $band->members()->count());
    }

    /** @test */
    public function it_respond_with_404_when_user_provided_unknown_invite_to_accept(): void
    {
        $this->actingAs($this->createUser());

        $this
            ->json('post', route('invites.accept', 10000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function user_can_accept_invite_to_band(): void
    {
        $user = $this->createUser();
        $band = $this->createBand();
        $role = 'guitarist';

        $invite = $this->createInvite([
            'user_id' => $user->id,
            'band_id' => $band->id,
            'role' => $role,
        ]);

        $this->assertEquals(1, Invite::count());
        $this->assertDatabaseHas('band_user_invites', ['user_id' => $user->id]);
        $this->assertEquals(0, $user->bands()->count());
        $this->assertEquals(0, $band->members()->count());

        $this->actingAs($user);

        $response = $this->json('post', route('invites.accept', $invite->id));

        $response->assertOk();

        $this->assertEquals(1, $user->bands()->count());
        $this->assertEquals($band->id, $user->bands->first()->id);
        $this->assertEquals(1, $band->members()->count());
        $this->assertEquals($user->id, $band->members()->first()->id);
        $this->assertEquals($role, $band->fresh()->members->first()->pivot->role);

        $this->assertDatabaseMissing('band_user_invites', ['user_id' => $user->id]);
        $this->assertEquals(0, Invite::count());
    }

    /** @test */
    public function when_user_accepts_band_invite_he_becomes_attendee_of_all_future_rehearsals_of_this_band(): void
    {
        $user = $this->createUser();
        $band = $this->createBand();

        $bandsRehearsalInPast = $this->createRehearsalForBandInThePast($band);
        $bandsRehearsalInFuture = $this->createRehearsalForBandInFuture($band);

        $this->assertEquals(2, $band->rehearsals()->count());

        $invite = $this->createInvite([
            'user_id' => $user->id,
            'band_id' => $band->id,
        ]);

        $this->actingAs($user);

        $this->assertEquals(0, $bandsRehearsalInPast->attendees()->count());
        $this->assertEquals(0, $bandsRehearsalInFuture->attendees()->count());

        $response = $this->json('post', route('invites.accept', $invite->id));

        $this->assertEquals(0, $bandsRehearsalInPast->attendees()->count());
        $this->assertEquals(1, $bandsRehearsalInFuture->attendees()->count());

        $response->assertOk();
    }
}

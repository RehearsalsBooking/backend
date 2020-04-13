<?php

namespace Tests\Feature\Invites;

use App\Models\Band;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class BandsMembersInviteTest.
 * @property User $bandAdmin
 * @property Band $band
 */
class DeclineInviteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthorized_user_cannot_decline_invite_to_band(): void
    {
        $this
            ->json('post', route('invites.decline', 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_can_decline_only_his_invite_to_band(): void
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
            ->json('post', route('invites.decline', $invite->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEquals(1, Invite::count());
        $this->assertDatabaseHas('band_user_invites', ['user_id' => $max->id]);
        $this->assertEquals(0, $max->bands()->count());
        $this->assertEquals(0, $john->bands()->count());
        $this->assertEquals(0, $band->members()->count());
    }

    /** @test */
    public function it_respond_with_404_when_user_provided_unknown_invite_to_decline(): void
    {
        $this->actingAs($this->createUser());

        $this
            ->json('post', route('invites.decline', 10000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function user_can_decline_invite_to_band(): void
    {
        $user = $this->createUser();
        $band = $this->createBand();
        $invite = $this->createInvite([
            'user_id' => $user->id,
            'band_id' => $band->id,
        ]);

        $this->assertEquals(1, Invite::count());
        $this->assertDatabaseHas('band_user_invites', ['user_id' => $user->id]);
        $this->assertEquals(0, $user->bands()->count());
        $this->assertEquals(0, $band->members()->count());

        $this->actingAs($user);

        $response = $this->json('post', route('invites.decline', $invite->id));

        $response->assertOk();

        $this->assertEquals(0, Invite::count());
        $this->assertDatabaseMissing('band_user_invites', ['user_id' => $user->id]);
        $this->assertEquals(0, $user->bands()->count());
        $this->assertEquals(0, $band->members()->count());
    }
}

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
 *
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
            ->json('post', route('users.invites.decline', 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_can_decline_only_his_invite_to_band(): void
    {
        $max = $this->createUser();
        $band = $this->createBand();
        $invite = $this->createInvite([
            'email' => $max->email,
            'band_id' => $band->id,
        ]);

        $john = $this->createUser();
        $this->actingAs($john);

        $this
            ->json('post', route('users.invites.decline', $invite->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEquals(1, Invite::count());
        $this->assertDatabaseHas('invites', ['email' => $max->email]);
        $this->assertEquals(0, $max->bands()->count());
        $this->assertEquals(0, $john->bands()->count());
        $this->assertEquals(0, $band->members()->count());
    }

    /** @test */
    public function it_respond_with_404_when_user_provided_unknown_invite_to_decline(): void
    {
        $this->actingAs($this->createUser());

        $this
            ->json('post', route('users.invites.decline', 10000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function user_can_decline_invite_to_band(): void
    {
        $user = $this->createUser();
        $band = $this->createBand();
        $invite = $this->createInvite([
            'email' => $user->email,
            'band_id' => $band->id,
        ]);

        $this->assertEquals(1, Invite::count());
        $this->assertDatabaseHas('invites', ['email' => $user->email]);
        $this->assertEquals(0, $user->bands()->count());
        $this->assertEquals(0, $band->members()->count());

        $this->actingAs($user);

        $response = $this->json('post', route('users.invites.decline', $invite->id));

        $response->assertOk();

        $this->assertEquals(1, Invite::count());
        $this->assertDatabaseHas('invites', ['email' => $user->email, 'status' => Invite::STATUS_DECLINED]);

        $this->assertEquals(0, $user->bands()->count());
        $this->assertEquals(0, $band->members()->count());
    }
}

<?php

namespace Tests\Feature\Bands\Invites;

use App\Models\Band;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class BandsMembersInviteTest
 * @package Tests\Feature\Bands
 * @property User $bandAdmin
 * @property Band $band
 */
class BandsMembersAcceptInviteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthorized_user_cannot_accept_invite(): void
    {
        $this
            ->json('post', route('invites.accept', 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_can_accept_only_his_invite(): void
    {
        $max = $this->createUser();
        $band = $this->createBand();
        $invite = $this->createInvite([
            'user_id' => $max->id,
            'band_id' => $band->id
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
    public function user_can_accept_invite(): void
    {
        $user = $this->createUser();
        $band = $this->createBand();
        $invite = $this->createInvite([
            'user_id' => $user->id,
            'band_id' => $band->id
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

        $this->assertDatabaseMissing('band_user_invites', ['user_id' => $user->id]);
        $this->assertEquals(0, Invite::count());
    }
}

<?php

namespace Tests\Feature\Bands\Invites;

use App\Models\Band;
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
class BandsMembersInviteTest extends TestCase
{
    use RefreshDatabase;

    private $bandAdmin;
    private $band;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bandAdmin = $this->createUser();
        $this->band = $this->createBandForUser($this->bandAdmin);

    }

    /** @test */
    public function admin_of_band_can_invite_users_to_his_band(): void
    {
        $this->actingAs($this->bandAdmin);

        $invitedUser = $this->createUser();

        $this->assertEquals(0, $this->band->invitedUsers()->count());
        $this->assertEquals(0, $invitedUser->invites()->count());

        $response = $this->json(
            'post',
            route('bands.invites.create', $this->band->id),
            [
                'user_id' => $invitedUser->id
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, $this->band->invitedUsers()->count());
        $this->assertEquals(1, $invitedUser->invites()->count());
        $this->assertEquals(
            collect([$invitedUser])->pluck('id'),
            $this->band->fresh()->invitedUsers->pluck('id')
        );
        $this->assertEquals(
            collect([$this->band])->pluck('id'),
            $invitedUser->fresh()->invites->pluck('id')
        );
    }

    /** @test */
    public function band_admin_can_cancel_member_invite(): void
    {
        $invitedUser = $this->createUser();

        $this->band->invite($invitedUser);

        $this->assertEquals(1, $this->band->invitedUsers()->count());
        $this->assertEquals(1, $invitedUser->invites()->count());

        $this->actingAs($this->bandAdmin);

        $response = $this->json(
            'delete',
            route('bands.invites.delete', $this->band->id),
            [
                'user_id' => $invitedUser->id
            ]
        );

        $response->assertOk();

        $this->assertEquals(0, $this->band->invitedUsers()->count());
        $this->assertEquals(0, $invitedUser->invites()->count());

    }
}

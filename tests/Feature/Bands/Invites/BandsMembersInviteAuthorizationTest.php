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
class BandsMembersInviteAuthorizationTest extends TestCase
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
    public function unauthenticated_user_cannot_invite_and_cancel_invite(): void
    {
        $this->json(
            'post',
            route('bands.invites.create', [1, 1])
        )->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->json(
            'delete',
            route('bands.invites.delete', [1, 1])
        )->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_admin_of_band_can_invite_and_cancel_invites(): void
    {
        $invite = $this->createInvite();

        $someOtherUser = $this->createUser();

        $this->actingAs($someOtherUser);

        $this->json(
            'post',
            route('bands.invites.create', $this->band->id)
        )->assertStatus(Response::HTTP_FORBIDDEN);

        $this->json(
            'delete',
            route('bands.invites.delete', [$this->band->id, $invite->id])
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function admin_of_a_band_can_cancel_only_his_bands_invites(): void
    {
        $someOtherBand = $this->createBandForUser($this->createUser());

        $invite = $this->createInvite(['band_id' => $someOtherBand->id]);

        $this->actingAs($this->bandAdmin);

        $this->json(
            'delete',
            route('bands.invites.delete', [$someOtherBand->id, $invite->id])
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

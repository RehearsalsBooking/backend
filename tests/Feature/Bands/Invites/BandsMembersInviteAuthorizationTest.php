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
            route('bands.invites.create', $this->band->id)
        )->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->json(
            'delete',
            route('bands.invites.delete', $this->band->id)
        )->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_admin_of_band_can_invite_and_cancel_invites(): void
    {
        $someOtherUser = $this->createUser();

        $this->actingAs($someOtherUser);

        $this->json(
            'post',
            route('bands.invites.create', $this->band->id),
            [
                'user_id' => 1
            ]
        )->assertStatus(Response::HTTP_FORBIDDEN);

        $this->json(
            'delete',
            route('bands.invites.delete', $this->band->id),
            [
                'user_id' => 1
            ]
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

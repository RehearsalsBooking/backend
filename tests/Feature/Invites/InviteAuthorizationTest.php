<?php

namespace Tests\Feature\Invites;

use App\Models\Band;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class InviteAuthorizationTest extends TestCase
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
    public function unauthenticated_user_cannot_invite_and_cancel_invite(): void
    {
        $this->json(
            'post',
            route('bands.invites.create', [$this->band])
        )->assertStatus(Response::HTTP_UNAUTHORIZED);

        $this->json(
            'delete',
            route('bands.invites.delete', [$this->band, 1])
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
            route('bands.invites.create', [$this->band]),
            [
                'band_id' => $this->band->id,
                'email' => 'some@email.com',
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->json(
            'delete',
            route('bands.invites.delete', [$this->band, $invite->id])
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
            route('bands.invites.delete', [$someOtherBand, $invite->id])
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

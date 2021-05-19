<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\BandInviteResource;
use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BandInvitesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_band_invites(): void
    {
        $bandManager = $this->createUser();
        $band = $this->createBandForUser($bandManager);

        $invitedUserEmail = 'some@email.com';
        $invite = $band->invite($invitedUserEmail);

        $this->createBand()->invite($invitedUserEmail);

        $this->assertDatabaseCount('invites', 2);

        $response = $this->actingAs($bandManager)->json('get', route('bands.invites.index', [$band]));
        $response->assertOk();
        $this->assertCount(1, $response->json());
        $this->assertEquals(
            BandInviteResource::collection([$invite])->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function only_band_admin_can_fetch_band_invites(): void
    {
        $this->json('get', route('bands.invites.index', [$this->createBand()]))
            ->assertUnauthorized();
        $this->actingAs($this->createUser())
            ->json('get', route('bands.invites.index', [$this->createBand()]))
            ->assertForbidden();
    }

    /** @test */
    public function it_filters_invites_by_status(): void
    {
        $bandManager = $this->createUser();
        $band = $this->createBandForUser($bandManager);

        $pendingInvite = $band->invite('pending@mail.com');
        $acceptedInvite = $band->invite('accepted@mail.com');
        $acceptedInvite->update(['status' => Invite::STATUS_ACCEPTED]);
        $rejectedInvite = $band->invite('rejected@mail.com');
        $rejectedInvite->update(['status' => Invite::STATUS_REJECTED]);

        $this->assertDatabaseCount('invites', 3);

        $response = $this->actingAs($bandManager)->json('get', route('bands.invites.index', [$band]), [
            'status' => [
                Invite::STATUS_PENDING,
                Invite::STATUS_ACCEPTED,
            ],
        ]);
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals($pendingInvite->id, $response->json('data.0.id'));
        $this->assertEquals($acceptedInvite->id, $response->json('data.1.id'));
    }
}

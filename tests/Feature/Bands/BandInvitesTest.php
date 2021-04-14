<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\BandInviteResource;
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
}

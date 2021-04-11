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

        $invitedUser = $this->createUser();
        $invite = $band->invite($invitedUser);

        $this->createBand()->invite($invitedUser);

        $this->assertDatabaseCount('band_user_invites', 2);

        $response = $this->actingAs($bandManager)->json('get', route('bands.invites', [$band]));
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
        $this->json('get', route('bands.invites', [$this->createBand()]))
            ->assertUnauthorized();
        $this->actingAs($this->createUser())
            ->json('get', route('bands.invites', [$this->createBand()]))
            ->assertForbidden();
    }
}

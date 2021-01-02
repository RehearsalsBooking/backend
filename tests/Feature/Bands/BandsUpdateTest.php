<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\BandResource;
use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class BandsUpdateTest.
 *
 * @property Band $band
 * @property User $bandOwner
 */
class BandsUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Band $band;
    private User $bandOwner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bandOwner = $this->createUser();
        $this->band = $this->createBandForUser($this->bandOwner);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_band(): void
    {
        $this->json('put', route('bands.update', $this->band))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_admin_of_a_band_can_update_it(): void
    {
        $this->actingAs($this->createUser());

        $this->json('put', route('bands.update', $this->band), ['name' => 'new band name'])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function admin_of_a_band_can_update_its_name(): void
    {
        $this->actingAs($this->bandOwner);

        $newBandData = [
            'name' => "band's new name",
        ];

        $this->assertDatabaseMissing('bands', $newBandData);

        $response = $this->json('put', route('bands.update', $this->band), $newBandData);

        $response->assertOk();

        $this->assertDatabaseHas('bands', $newBandData);

        $this->assertEquals(
            $this->band->fresh()->name,
            $newBandData['name']
        );

        $this->assertEquals(
            (new BandResource($this->band->fresh()))->response()->getData(true),
            $response->json()
        );
    }
}

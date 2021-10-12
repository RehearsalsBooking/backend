<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\BandDetailedResource;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class BandsShowTest extends TestCase
{
    private User $bandOwner;

    /** @test */
    public function it_fetches_single_band(): void
    {
        $band = $this->createBand();

        $response = $this->json('get', route('bands.show', $band));
        $response->assertOk();

        $this->assertEquals(
            (new BandDetailedResource($band))->response()->getData(true)['data'],
            $response->json('data')
        );
    }

    /** @test */
    public function it_indicates_that_current_user_is_admin(): void
    {
        $bandAdmin = $this->createUser();
        $band = $this->createBand(['admin_id' => $bandAdmin->id]);

        $response = $this->json('get', route('bands.show', $band));
        $this->assertFalse($response->json('data.is_admin'));

        $response = $this->actingAs($this->createUser())->json('get', route('bands.show', $band));
        $this->assertFalse($response->json('data.is_admin'));

        $response = $this->actingAs($bandAdmin)->json('get', route('bands.show', $band));
        $this->assertTrue($response->json('data.is_admin'));
    }

    /** @test */
    public function it_responds_with_404_when_unknown_band_is_given(): void
    {
        $this->json('get', route('bands.show', 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json('get', route('bands.show', 'some text'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

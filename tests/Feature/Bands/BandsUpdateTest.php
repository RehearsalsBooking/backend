<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\BandDetailedResource;
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
    public function admin_of_a_band_can_update_band(): void
    {
        $this->actingAs($this->bandOwner);

        $newGenres = collect([$this->createGenre(), $this->createGenre()]);
        $newBandData = [
            'name' => "band's new name",
            'bio' => 'new bio',
        ];

        $this->assertDatabaseMissing('bands', $newBandData);

        $response = $this->json(
            'put',
            route('bands.update', $this->band),
            array_merge($newBandData, ['genres' => $newGenres->pluck('id')->toArray()])
        );

        $response->assertOk();

        $this->assertDatabaseHas('bands', $newBandData);

        $this->assertEquals(
            $this->band->fresh()->name,
            $newBandData['name']
        );
        $this->assertEquals(
            $this->band->fresh()->bio,
            $newBandData['bio']
        );

        $this->assertEquals(
            $this->band->fresh()->genres->pluck('id')->toArray(),
            $newGenres->pluck('id')->toArray()
        );

        $this->assertEquals(
            (new BandDetailedResource($this->band->fresh()))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function it_responds_with_422_when_unknown_genre_id_is_provided(): void
    {
        $unknownGenreId = 999;

        $this->assertDatabaseMissing('genres', ['id' => $unknownGenreId]);

        $this->actingAs($this->bandOwner)->json(
            'put',
            route('bands.update', $this->band),
            ['genres' => [$unknownGenreId]]
        )
            ->assertJsonValidationErrors('genres.0');
    }

    /** @test */
    public function name_cannot_be_null(): void
    {
        $this->actingAs($this->bandOwner)->json(
            'put',
            route('bands.update', $this->band),
            ['name' => null]
        )
            ->assertJsonValidationErrors('name');
        $this->actingAs($this->bandOwner)->json(
            'put',
            route('bands.update', $this->band),
            ['name' => '']
        )
            ->assertJsonValidationErrors('name');
    }
}

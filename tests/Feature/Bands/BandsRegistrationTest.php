<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\BandDetailedResource;
use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class BandsRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
    }

    /** @test */
    public function unauthenticated_user_cannot_create_a_band(): void
    {
        $this->json('post', route('bands.create'))->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function authenticated_user_can_create_band(): void
    {
        $this->actingAs($this->user);

        $this->assertEquals(0, Band::count());

        $genres = collect([$this->createGenre(), $this->createGenre()]);
        $newBandAttributes = [
            'name' => "band's new name",
            'bio' => 'new bio',
        ];

        $response = $this->json('post', route('bands.create'), array_merge(
            $newBandAttributes,
            ['genres' => $genres->pluck('id')->toArray()]
        ));

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('bands', $newBandAttributes);
        $this->assertEquals(1, Band::count());

        $newBand = Band::first();

        $this->assertEquals(
            $newBand->name,
            $newBandAttributes['name']
        );
        $this->assertEquals(
            $newBand->bio,
            $newBandAttributes['bio']
        );

        $this->assertEquals(
            $newBand->genres->pluck('id')->toArray(),
            $genres->pluck('id')->toArray()
        );

        $this->assertEquals(
            (new BandDetailedResource($newBand))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function when_user_creates_band_he_becomes_its_admin(): void
    {
        $this->actingAs($this->user);

        $this->json('post', route('bands.create'), ['name' => 'some name']);

        $newBand = Band::first();

        $this->assertEquals(
            $newBand->admin_id,
            $this->user->id
        );
    }

    /** @test */
    public function user_cannot_create_band_without_a_name(): void
    {
        $this->actingAs($this->user);

        $this->assertEquals(0, Band::count());

        $this->json('post', route('bands.create'))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function it_responds_with_422_when_unknown_genre_id_is_provided(): void
    {
        $unknownGenreId = 999;

        $this->assertDatabaseMissing('band_genres', ['id' => $unknownGenreId]);

        $this->actingAs($this->user)->json(
            'post',
            route('bands.create'),
            [
                'name' => 'some name',
                'genres' => [$unknownGenreId]
            ]
        )
            ->assertJsonValidationErrors('genres.0');
    }

    /** @test */
    public function when_user_creates_band_he_joins_it_automatically(): void
    {
        $this->actingAs($this->user);

        $this->json('post', route('bands.create'), ['name' => 'band name']);

        $createdBand = Band::first();

        $this->assertEquals(
            $createdBand->members->pluck('id'),
            collect([$this->user])->pluck('id')
        );
    }
}

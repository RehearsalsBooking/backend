<?php

namespace Tests\Feature;

use App\Http\Resources\Users\BandResource;
use App\Models\Band;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BandsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_user_cannot_create_a_band(): void
    {
        $this->json('post', route('bands.create'))->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function authenticated_user_can_create_band(): void
    {
        $this->actingAs($this->createUser());

        $this->assertEquals(0, Band::count());

        $newBandAttributes = [
            'name' => 'some new band name'
        ];

        $response = $this->json('post', route('bands.create'), $newBandAttributes);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('bands', $newBandAttributes);
        $this->assertEquals(1, Band::count());

        $newBand = Band::first();

        $this->assertEquals(
            (new BandResource($newBand))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function when_user_creates_band_he_becomes_its_admin(): void
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $this->json('post', route('bands.create'), ['name' => 'some name']);

        $newBand = Band::first();

        $this->assertEquals(
            $newBand->admin->toArray(),
            $user->toArray()
        );

        $this->assertEquals(
            $newBand->admin_id,
            $user->id
        );
    }

    /** @test */
    public function user_cannot_create_band_without_a_name(): void
    {
        $this->actingAs($this->createUser());

        $this->assertEquals(0, Band::count());

        $this->json('post', route('bands.create'))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('name');
    }
}

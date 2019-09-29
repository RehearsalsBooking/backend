<?php

namespace Tests\Feature\Bands;

use App\Http\Resources\Users\BandResource;
use App\Models\Band;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class BandsRegistrationTest
 * @property User $user
 * @package Tests\Feature\Bands
 */
class BandsRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;

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
        $this->actingAs($this->user);

        $this->json('post', route('bands.create'), ['name' => 'some name']);

        $newBand = Band::first();

        $this->assertEquals(
            $newBand->admin->toArray(),
            $this->user->toArray()
        );

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

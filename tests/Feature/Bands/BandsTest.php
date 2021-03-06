<?php

namespace Tests\Feature\Bands;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Throwable;

/**
 * @property Band $band
 * @property User $bandOwner
 */
class BandsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_fetch_bands(): void
    {
        $bandsCount = 3;

        Band::factory()->count($bandsCount)->create();

        $this->assertEquals($bandsCount, Band::count());

        $response = $this->get(route('bands.list'));
        $response->assertOk();

        $this->assertCount($bandsCount, $response->json('data'));
    }

    /** @test */
    public function it_fetches_correct_band_members_count(): void
    {
        $band = $this->createBand();
        $band->addMember($this->createUser()->id);
        $band->addMember($this->createUser()->id);

        $this->assertEquals(2, $band->fresh()->members()->count());
        $response = $this->get(route('bands.list'));
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(2, $response->json('data.0.members_count'));
    }

    /** @test */
    public function it_fetches_band_genres(): void
    {
        $band = $this->createBand();
        $bandGenres = collect([$this->createGenre(), $this->createGenre()]);
        $band->genres()->attach($bandGenres->pluck('id'));

        $this->assertEquals(2, $band->fresh()->genres()->count());
        $response = $this->get(route('bands.list'));
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $fetchedGenres = $response->json('data.0.genres');
        $this->assertCount(2, $fetchedGenres);
        $this->assertEquals($bandGenres->pluck('id'), collect($fetchedGenres)->pluck('id'));
    }

    /** @test
     * @throws Throwable
     */
    public function users_can_filter_bands_by_member(): void
    {
        $user = $this->createUser();
        $this->createBand();
        $participatingBand = $this->createBand();
        $participatingBand->addMember($user->id);

        $this->assertEquals(1, $user->bands()->count());
        $this->assertEquals(2, Band::count());

        $response = $this->get(route('bands.list', ['member_id' => $user->id]));

        $response->assertOk();

        $fetchedBands = $response->json('data');

        $this->assertCount(1, $fetchedBands);
        $this->assertEquals($participatingBand->id, $fetchedBands[0]['id']);
    }

    /** @test */
    public function member_id_in_bands_filter_may_be_only_integer(): void
    {
        $response = $this->json('get', route('bands.list', ['member_id' => 'text']));
        $response->assertJsonValidationErrors('member_id');
    }
}

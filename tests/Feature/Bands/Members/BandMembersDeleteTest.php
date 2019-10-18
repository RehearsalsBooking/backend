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
 * @property User $bandAdmin
 * @property Band $band
 * @package Tests\Feature\Bands
 */
class BandMembersDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;
    /**
     * @var Band
     */
    private $band;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bandAdmin = $this->createUser();
        $this->band = $this->createBandForUser($this->bandAdmin);
    }

    /** @test */
    public function band_admin_can_remove_bands_members(): void
    {
        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount);
        $this->band->members()->saveMany($bandMembers);

        $this->actingAs($this->bandAdmin);

        $this->assertEquals($bandMembersCount, $this->band->members()->count());
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $this->band->members->pluck('id')->toArray()
        );

        $userIdToRemoveFromBand = $this->band->members()->inRandomOrder()->first(['id'])->id;

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $userIdToRemoveFromBand]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals($bandMembersCount - 1, $this->band->members()->count());
        $this->assertNotContains(
            $userIdToRemoveFromBand,
            $this->band->fresh(['members'])->pluck('id')->toArray()
        );
    }
}

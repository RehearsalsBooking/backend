<?php

namespace Tests\Feature\Bands\Members;

use App\Models\Band;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BandMembersIndexTest extends TestCase
{
    use RefreshDatabase;

    private Band $band;

    protected function setUp(): void
    {
        parent::setUp();

        $this->band = $this->createBand();
    }

    /** @test */
    public function it_fetches_band_members(): void
    {
        $max = $this->createUser();
        $john = $this->createUser();
        $this->band->addMember($max->id);
        $this->band->addMember($john->id);

        $response = $this->json('get', route('bands.members.index', [$this->band]));
        $response->assertOk();

        $this->assertCount(2, $response->json('data'));
    }
}

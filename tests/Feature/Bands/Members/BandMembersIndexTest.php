<?php

namespace Tests\Feature\Bands\Members;

use App\Http\Resources\Users\BandMembershipResource;
use App\Models\Band;
use Tests\TestCase;

class BandMembersIndexTest extends TestCase
{
    private Band $band;

    protected function setUp(): void
    {
        parent::setUp();

        $this->band = $this->createBand();
    }

    /** @test */
    public function it_fetches_band_memberships(): void
    {
        $max = $this->createUser();
        $john = $this->createUser();
        $this->band->addMember($max->id);
        $this->band->addMember($john->id);

        $response = $this->json('get', route('bands.members.index', [$this->band]));
        $response->assertOk();

        $this->assertCount(3, $response->json('data'));
        $this->assertEquals(
            BandMembershipResource::collection($this->band->memberships)->response()->getData(true),
            $response->json()
        );
    }
}

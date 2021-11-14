<?php

namespace Tests\Feature;

use App\Http\Resources\RoomResource;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use Tests\TestCase;

class FetchRoomsTest extends TestCase
{
    private string $endpoint = 'organizations.rooms';
    private string $httpVerb = 'get';

    /** @test */
    public function it_fetches_rooms_of_organization(): void
    {
        $organization = $this->createOrganization();
        $room1 = $this->createOrganizationRoom($organization);
        $room2 = $this->createOrganizationRoom($organization);

        $anotherOrganization = $this->createOrganization();
        $this->createOrganizationRoom($anotherOrganization);
        $this->createOrganizationRoom($anotherOrganization);

        $this->assertDatabaseCount(OrganizationRoom::class, 4);

        $response = $this->json($this->httpVerb, route($this->endpoint, [$organization]));
        $response->assertOk();

        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(
            RoomResource::collection(collect([$room1, $room2]))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_provided(): void
    {
        $unknownOrganizationId = 1000;
        $this->assertDatabaseMissing(Organization::class, ['id' => $unknownOrganizationId]);
        $this->json($this->httpVerb, route($this->endpoint, [$unknownOrganizationId]))->assertNotFound();
    }
}

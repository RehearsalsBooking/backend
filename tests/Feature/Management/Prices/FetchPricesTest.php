<?php

namespace Tests\Feature\Management\Prices;

use App\Http\Resources\RoomPriceResource;
use App\Models\Organization\OrganizationRoomPrice;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class FetchPricesTest extends TestCase
{
    private string $endpoint = 'rooms.prices.list';
    private string $httpVerb = 'get';

    /** @test */
    public function it_responds_with_404_when_unknown_organization_room_is_given(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function users_can_fetch_prices_of_organization_room(): void
    {
        $organization = $this->createOrganization();
        $organizationRoom = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);

        $otherOrganization = $this->createOrganization();
        $this->createOrganizationRoom($otherOrganization);
        $this->createPricesForOrganization($otherOrganization);

        $organizationRoom->prices()->whereIn('day', [5, 6])->delete();
        $this->assertEquals(5, $organizationRoom->prices()->count());
        $this->assertEquals(5 + 7, OrganizationRoomPrice::count());

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $organizationRoom->id)
        );
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(
            RoomPriceResource::collection($organizationRoom->prices)->response()->getData(true),
            $response->json()
        );
    }
}

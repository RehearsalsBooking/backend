<?php

namespace Tests\Feature\Management\Prices;

use App\Http\Resources\RoomPriceResource;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Management\ManagementTestCase;

class FetchPricesTest extends ManagementTestCase
{
    private string $endpoint = 'management.rooms.prices.list';
    private string $httpVerb = 'get';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_room_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $anotherOrganization = $this->createOrganizationForUser($managerOfAnotherOrganization);
        $this->createOrganizationRoom($anotherOrganization);

        $this->actingAs($ordinaryClient);
        $this->json($this->httpVerb, route($this->endpoint, $this->organizationRoom->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json($this->httpVerb, route($this->endpoint, $this->organizationRoom->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_room_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function manager_of_organization_can_get_prices_of_his_organization_room(): void
    {
        $this->assertEquals(5, $this->organizationRoom->prices()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organizationRoom->id)
        );
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(
            RoomPriceResource::collection($this->organizationRoom->prices)->response()->getData(true),
            $response->json()
        );
    }
}

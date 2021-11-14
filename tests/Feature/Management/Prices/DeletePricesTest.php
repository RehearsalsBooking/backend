<?php

namespace Tests\Feature\Management\Prices;

use App\Models\Organization\OrganizationRoom;
use App\Models\Organization\OrganizationRoomPrice;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Management\ManagementTestCase;

class DeletePricesTest extends ManagementTestCase
{
    private string $endpoint = 'management.rooms.prices.delete';
    private string $httpVerb = 'delete';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, [1, 1]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_room_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $anotherOrganization = $this->createOrganizationForUser($managerOfAnotherOrganization);
        $this->createOrganizationRoom($anotherOrganization);

        $priceId = $this->organizationRoom->prices()->first()->id;

        $this->actingAs($ordinaryClient);

        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organizationRoom->id, $priceId])
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organizationRoom->id, $priceId])
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function deleting_price_should_be_owned_by_organization_room(): void
    {
        $managerOfAnotherOrganization = $this->createUser();
        $anotherOrganization = $this->createOrganizationForUser($managerOfAnotherOrganization);
        $anotherOrganizationRoom = $this->createOrganizationRoom($anotherOrganization);

        $priceIdToDelete = $this->organizationRoom->prices()->first()->id;

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$anotherOrganizationRoom->id, $priceIdToDelete])
        )
            ->assertNotFound();
    }

    /** @test */
    public function it_responds_with_404_when_unknown_price_or_room_is_given(): void
    {
        $priceId = $this->organizationRoom->prices()->where('day', 1)->first()->id;

        $this->actingAs($this->manager);

        $unknownPriceId = 10000;
        $unknownRoomId = 10000;

        $this->assertEquals(0, OrganizationRoomPrice::where('id', $unknownPriceId)->count());
        $this->assertEquals(0, OrganizationRoom::where('id', $unknownRoomId)->count());

        $this->json($this->httpVerb, route($this->endpoint, [$this->organizationRoom->id, $unknownPriceId]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, [$unknownRoomId, $priceId]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, [$this->organizationRoom->id, 'some text']))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, ['some text', $priceId]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function manager_of_organization_can_delete_price_entry_of_his_organization_room(): void
    {
        $this->assertEquals(5, $this->organizationRoom->prices()->count());

        $priceId = $this->organizationRoom->prices()->where('day', 1)->first()->id;

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organizationRoom->id, $priceId])
        );
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals(4, $this->organizationRoom->prices()->count());
        $this->assertDatabaseMissing('organization_room_prices', [
            'day' => 1,
            'organization_room_id' => $this->organizationRoom->id,
        ]);
    }
}

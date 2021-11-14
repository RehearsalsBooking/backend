<?php

namespace Tests\Feature\Management\Rooms;

use App\Http\Resources\RoomResource;
use App\Models\Organization\OrganizationRoom;
use Tests\Feature\Management\ManagementTestCase;

class UpdateRoomTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.rooms.update';
    private string $httpVerb = 'put';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $roomData = $this->organization->rooms()->first()->toArray();
        $this->json($this->httpVerb, route($this->endpoint, [1, 1]))
            ->assertUnauthorized();
        $this->assertEquals(1, $this->organization->rooms()->count());
        $this->assertDatabaseHas(OrganizationRoom::class, $roomData);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);

        $roomData = $this->organization->rooms()->first()->toArray();

        $this->actingAs($ordinaryClient);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $this->organizationRoom->id]),
            $this->getNewRoomAttributes()
        )
            ->assertForbidden();

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $this->organizationRoom->id]),
            $this->getNewRoomAttributes()
        )
            ->assertForbidden();

        $this->assertEquals(1, $this->organization->rooms()->count());
        $this->assertDatabaseHas(OrganizationRoom::class, $roomData);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_or_room_is_given(): void
    {
        $roomData = $this->organization->rooms()->first()->toArray();

        $roomOfAnotherOrganization = $this->createOrganizationRoom();

        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, [1000, $this->organizationRoom->id]))
            ->assertNotFound();
        $this->json($this->httpVerb, route($this->endpoint, [$this->organization->id, 1000]))
            ->assertNotFound();
        $this->json($this->httpVerb, route($this->endpoint, ['text', $this->organizationRoom->id]))
            ->assertNotFound();
        $this->json($this->httpVerb, route($this->endpoint, [$this->organization->id, 'text']))
            ->assertNotFound();
        $this->json(
            $this->httpVerb,
            route(
                $this->endpoint,
                [$this->organization->id, $roomOfAnotherOrganization->id]
            ),
            $this->getNewRoomAttributes()
        )
            ->assertNotFound();

        $this->assertEquals(1, $this->organization->rooms()->count());
        $this->assertDatabaseHas(OrganizationRoom::class, $roomData);
    }

    /**
     * @test
     */
    public function it_responds_with_422_when_manager_provided_invalid_data(): void
    {
        $roomData = $this->organization->rooms()->first()->toArray();

        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $this->organizationRoom->id]),
            []
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->assertEquals(1, $this->organization->rooms()->count());
        $this->assertDatabaseHas(OrganizationRoom::class, $roomData);
    }

    /** @test */
    public function manager_of_organization_can_change_room_of_his_organization(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());
        $roomData = $this->organization->rooms()->first()->toArray();

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $this->organizationRoom->id]),
            $this->getNewRoomAttributes()
        );
        $response->assertOk();

        $this->assertEquals(1, $this->organization->rooms()->count());
        $this->assertDatabaseMissing(OrganizationRoom::class, $roomData);
        $this->assertDatabaseHas(OrganizationRoom::class, array_merge(
            $this->getNewRoomAttributes(),
            ['organization_id' => $this->organization->id]
        ));
        $this->assertEquals(
            (new RoomResource($this->organization->rooms()->where($this->getNewRoomAttributes())->first()))
                ->response()
                ->getData(true),
            $response->json()
        );
    }

    protected function getNewRoomAttributes(): array
    {
        return ['name' => 'room'];
    }
}

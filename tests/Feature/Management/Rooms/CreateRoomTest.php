<?php

namespace Tests\Feature\Management\Rooms;

use App\Http\Resources\RoomResource;
use Tests\Feature\Management\ManagementTestCase;

class CreateRoomTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.rooms.create';
    private string $httpVerb = 'post';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertUnauthorized();
        $this->assertEquals(1, $this->organization->rooms()->count());
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);

        $this->assertEquals(1, $this->organization->rooms()->count());

        $this->actingAs($ordinaryClient);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            $this->getNewRoomAttributes()
        )
            ->assertForbidden();

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            $this->getNewRoomAttributes()
        )
            ->assertForbidden();

        $this->assertEquals(1, $this->organization->rooms()->count());
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_is_given(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());

        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertNotFound();
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertNotFound();

        $this->assertEquals(1, $this->organization->rooms()->count());
    }

    /**
     * @test
     */
    public function it_responds_with_422_when_manager_provided_invalid_data(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());

        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            []
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->assertEquals(1, $this->organization->rooms()->count());
    }

    /** @test */
    public function manager_of_organization_can_add_room_to_his_organization(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            $this->getNewRoomAttributes()
        );
        $response->assertCreated();

        $this->assertEquals(2, $this->organization->rooms()->count());
        $this->assertDatabaseHas('organization_rooms', array_merge(
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

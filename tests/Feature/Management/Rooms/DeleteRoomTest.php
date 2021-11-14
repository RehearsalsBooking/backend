<?php

namespace Tests\Feature\Management\Rooms;

use App\Http\Resources\RoomResource;
use App\Models\Organization\OrganizationRoom;
use Carbon\Carbon;
use Tests\Feature\Management\ManagementTestCase;

class DeleteRoomTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.rooms.delete';
    private string $httpVerb = 'delete';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());
        $this->json($this->httpVerb, route($this->endpoint, [1, 1]))
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
            route($this->endpoint, [$this->organization->id, $this->organizationRoom->id])
        )
            ->assertForbidden();

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $this->organizationRoom->id])
        )
            ->assertForbidden();

        $this->assertEquals(1, $this->organization->rooms()->count());
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_or_room_is_given(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());

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
            )
        )
            ->assertNotFound();

        $this->assertEquals(1, $this->organization->rooms()->count());
    }

    /** @test */
    public function manager_of_organization_cannot_delete_room_if_it_has_any_rehearsals_in_future(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());

        $this->createRehearsalForUserInFuture(
            room: $this->organizationRoom
        );

        $this->assertEquals(1, $this->organizationRoom->futureRehearsals()->count());

        $this->actingAs($this->manager);
        $this->json(
            $this->httpVerb,
            route(
                $this->endpoint,
                [$this->organization->id, $this->organizationRoom->id]
            )
        )->assertForbidden();

        $this->assertEquals(1, $this->organization->rooms()->count());
    }

    /** @test */
    public function manager_of_organization_can_delete_room_of_his_organization(): void
    {
        $this->assertEquals(1, $this->organization->rooms()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $this->organizationRoom->id]),
        );
        $response->assertNoContent();

        $this->assertEquals(0, $this->organization->rooms()->count());
    }
}

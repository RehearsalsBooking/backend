<?php

namespace Tests\Feature\Management\Rehearsals;

use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class DeleteRehearsalTest extends ManagementTestCase
{
    private string $endpoint = 'management.rehearsal.delete';
    private string $httpVerb = 'delete';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);
        $rehearsal = $this->createRehearsal(
            1,
            2,
            $this->organization,
            null,
            false,
            $ordinaryClient
        );

        $this->actingAs($ordinaryClient);
        $this->json($this->httpVerb, route($this->endpoint, $rehearsal->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json($this->httpVerb, route($this->endpoint, $rehearsal->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_rehearsal_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function manager_of_organization_can_delete_rehearsal_of_his_organization(): void
    {
        $rehearsal = $this->createRehearsal(1, 2, $this->organization);

        $this->assertEquals(1, $this->organization->rehearsals()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $rehearsal->id)
        );
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals(0, $this->organization->rehearsals()->count());
    }
}

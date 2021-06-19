<?php

namespace Tests\Feature\Management\Rehearsals;

use App\Http\Resources\RehearsalDetailedResource;
use App\Models\Organization\Organization;
use App\Models\Rehearsal;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class FetchRehearsalsTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.rehearsals';
    private string $httpVerb = 'get';
    private Organization $anotherOrganization;

    protected function setUp(): void
    {
        parent::setUp();

        // create two rehearsals for organization
        $this->createRehearsalsForOrganization($this->organization, 2);

        // create two rehearsals for another organization
        $this->anotherOrganization = $this->createOrganization();
        $this->createRehearsalsForOrganization($this->anotherOrganization, 2);
    }

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        Rehearsal::truncate();

        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);
        $this->createRehearsal(
            1,
            2,
            $this->organization,
            null,
            false,
            $ordinaryClient
        );

        $this->actingAs($ordinaryClient);
        $this->json($this->httpVerb, route($this->endpoint, [$this->organization]))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json($this->httpVerb, route($this->endpoint, $this->organization))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function manager_of_organization_can_fetch_rehearsals_of_his_organization(): void
    {
        $this->assertEquals(2, $this->organization->rehearsals()->count());
        $this->assertEquals(4, Rehearsal::count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization)
        );
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals(
            RehearsalDetailedResource::collection($this->organization->rehearsals()->paginate())->response()->getData(true)['data'],
            $data
        );
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_manager_tries_to_fetch_another_organizations_rehearsals(
    ): void
    {
        $this->actingAs($this->manager);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->anotherOrganization)
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->json(
            $this->httpVerb,
            route($this->endpoint, 'some_id')
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);

        $this->json(
            $this->httpVerb,
            route($this->endpoint, 'organization_id')
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

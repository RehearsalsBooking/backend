<?php

namespace Tests\Feature\Management\Organizations;

use App\Http\Resources\Management\OrganizationResource;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class ShowOrganizationTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.show';
    private string $httpVerb = 'get';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_manager_can_fetch_his_organization(): void
    {
        $this->actingAs($this->createUser())
            ->json($this->httpVerb, route($this->endpoint, [$this->organization]))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);
        $this->actingAs($managerOfAnotherOrganization)
            ->json($this->httpVerb, route($this->endpoint, [$this->organization]))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function manager_of_organization_can_fetch_his_organization(): void
    {
        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization])
        );
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json('data');

        $this->assertEquals(
            (new OrganizationResource($this->organization))->response()->getData(true)['data'],
            $data
        );
    }
}

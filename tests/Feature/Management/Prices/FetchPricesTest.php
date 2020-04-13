<?php

namespace Tests\Feature\Management\Rehearsals;

use App\Http\Resources\OrganizationPriceResource;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class FetchPricesTest extends ManagementTestCase
{
    private string $endpoint = 'management.organization.prices.list';
    private string $httpVerb = 'get';

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

        $this->actingAs($ordinaryClient);
        $this->json($this->httpVerb, route($this->endpoint, $this->organization->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json($this->httpVerb, route($this->endpoint, $this->organization->id))
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
    public function manager_of_organization_can_get_prices_of_his_organization(): void
    {
        $this->assertEquals(5, $this->organization->prices()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id)
        );
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(
            OrganizationPriceResource::collection($this->organization->prices)->response()->getData(true),
            $response->json()
        );
    }
}

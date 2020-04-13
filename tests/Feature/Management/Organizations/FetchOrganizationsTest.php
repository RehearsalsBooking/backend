<?php

namespace Tests\Feature\Management\Rehearsals;

use App\Http\Resources\Management\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

/**
 * Class FetchRehearsalsTest
 * {@inheritdoc}
 *
 * @property Organization $anotherOrganization
 */
class FetchOrganizationsTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.list';
    private string $httpVerb = 'get';
    private Organization $anotherOrganization;

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function manager_of_organization_can_fetch_only_his_organizations(): void
    {
        $this->assertEquals(3, Organization::count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint)
        );
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals(
            OrganizationResource::collection($this->manager->organizations)->response()->getData(true)['data'],
            $data
        );
    }

    /** @test */
    public function manager_fetches_active_and_inactive_organizations(): void
    {
        $inactiveOrganization = $this->createOrganizationForUser($this->manager, ['is_active' => false]);

        $this->assertEquals(4, Organization::withoutGlobalScopes()->count());
        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint)
        );
        $response->assertStatus(Response::HTTP_OK);

        $data = $response->json('data');

        $this->assertCount(3, $data);
        $this->assertContains($inactiveOrganization->id, collect($data)->pluck('id')->toArray());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createOrganizationForUser($this->createUser());
    }
}

<?php

namespace Tests\Feature;

use App\Http\Resources\Users\OrganizationDetailResource;
use App\Http\Resources\Users\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_view_organizations(): void
    {
        $this->withoutExceptionHandling();
        $numberOfOrganizations = 5;
        factory(Organization::class, $numberOfOrganizations)->create();

        $this->assertCount($numberOfOrganizations, Organization::all());

        $response = $this->get(route('organizations.list'));

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount(5, $data);
        $this->assertEquals(
            OrganizationResource::collection(Organization::all())->toArray(null),
            $data
        );
    }

    /** @test */
    public function users_can_view_detailed_information_about_organization(): void
    {
        $organization = $this->createOrganization();

        $response = $this->get(route('organizations.show', $organization->id));

        $response->assertOk();

        $this->assertEquals(
            (new OrganizationDetailResource($organization))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function it_responds_with_404_when_user_fetches_organization_with_unknown_id(): void
    {
        $this->get(route('organizations.show', 1000))->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get(route('organizations.show', 'asd'))->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function users_can_see_only_verified_organizations(): void
    {
        $numberOfActiveOrganizations = 3;
        $numberOfInactiveOrganizations = 2;

        factory(Organization::class, $numberOfActiveOrganizations)->create([
            'active' => true,
        ]);

        factory(Organization::class, $numberOfInactiveOrganizations)->create([
            'active' => false,
        ]);

        $this->assertEquals(
            $numberOfInactiveOrganizations + $numberOfActiveOrganizations,
            Organization::withoutGlobalScopes()->count()
        );

        $response = $this->get(route('organizations.list'));

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount($numberOfActiveOrganizations, $data);
        $this->assertEquals(
            OrganizationResource::collection(Organization::get())->toArray(null),
            $data
        );
    }
}

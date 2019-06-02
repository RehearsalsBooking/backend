<?php

namespace Tests\Feature;

use App\Http\Resources\Users\OrganizationResource;
use App\Models\Organization;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unsigned_users_can_view_organizations(): void
    {
        $numberOfOrganizations = 5;
        factory(Organization::class, $numberOfOrganizations)->create([
            'verified' => true
        ]);

        $this->assertCount($numberOfOrganizations, Organization::all());

        $response = $this->get('/organizations');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount(5, $data);
        $this->assertEquals(
            OrganizationResource::collection(Organization::all())->toArray(null),
            $data
        );
    }

    /** @test */
    public function unsigned_users_can_see_only_verified_organizations(): void
    {
        $numberOfVerifiedOrganizations = 3;

        factory(Organization::class, $numberOfVerifiedOrganizations)->create([
            'verified' => true,
        ]);

        factory(Organization::class, 2)->create([
            'verified' => false,
        ]);

        $this->assertCount(5, Organization::all());

        $response = $this->get('/organizations');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertCount($numberOfVerifiedOrganizations, $data);
        $this->assertEquals(
            OrganizationResource::collection(Organization::verified()->get())->toArray(null),
            $data
        );
    }
}

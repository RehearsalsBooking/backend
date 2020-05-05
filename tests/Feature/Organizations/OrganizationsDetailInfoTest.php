<?php

namespace Tests\Feature\Organizations;

use App\Http\Resources\OrganizationPriceResource;
use App\Http\Resources\Users\OrganizationDetailResource;
use App\Http\Resources\Users\OwnerResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationsDetailInfoTest extends TestCase
{
    use RefreshDatabase;

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
    public function users_can_view_prices_of_organization_in_detailed_information_about_organization(): void
    {
        $organization = $this->createOrganization();
        $this->createPricesForOrganization($organization);

        $response = $this->get(route('organizations.show', $organization->id));

        $response->assertOk();

        $this->assertArrayHasKey('prices', $response->json('data'));
        $this->assertEquals(
            (OrganizationPriceResource::collection($organization->prices))->response()->getData(true)['data'],
            $response->json('data.prices')
        );
    }

    /** @test */
    public function users_can_view_owner_info_in_detailed_view(): void
    {
        $owner = $this->createUser();
        $organization = $this->createOrganizationForUser($owner);

        $response = $this->get(route('organizations.show', $organization->id));

        $response->assertOk();

        $this->assertArrayHasKey('owner', $response->json('data'));
        $this->assertEquals(
            (new OwnerResource($owner))->response()->getData(true)['data'],
            $response->json('data.owner')
        );
    }

    /** @test */
    public function user_gets_correct_information_about_favorite_organization_in_detailed_resource(): void
    {
        $this->withoutExceptionHandling();
        $organization = $this->createOrganization();
        $user = $this->createUser();

        $user->favoriteOrganizations()->sync($organization);

        // when guest is fetching
        $response = $this->get(route('organizations.show', $organization->id));

        $response->assertOk();

        $isFavorited = $response->json('data.is_favorited');
        $this->assertFalse($isFavorited);

        // when logged in user fetching
        $this->actingAs($user);

        $response = $this->get(route('organizations.show', $organization->id));

        $response->assertOk();

        $isFavorited = $response->json('data.is_favorited');
        $this->assertTrue($isFavorited);
    }
}

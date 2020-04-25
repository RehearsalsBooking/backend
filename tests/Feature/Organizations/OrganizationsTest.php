<?php

namespace Tests\Feature\Organizations;

use App\Http\Resources\OrganizationPriceResource;
use App\Http\Resources\Users\OrganizationDetailResource;
use App\Http\Resources\Users\OrganizationResource;
use App\Models\Organization\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class OrganizationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_view_organizations(): void
    {
        $numberOfOrganizations = 5;
        factory(Organization::class, $numberOfOrganizations)->create();

        $this->assertCount($numberOfOrganizations, Organization::all());

        $response = $this->get(route('organizations.list'));

        $response->assertOk();

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
    public function it_responds_with_404_when_user_fetches_organization_with_unknown_id(): void
    {
        $this->assertEquals(0, Organization::count());
        $this->get(route('organizations.show', 1000))->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get(route('organizations.show', 'asd'))->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function users_can_see_only_active_organizations(): void
    {
        $numberOfActiveOrganizations = 3;
        $numberOfInactiveOrganizations = 2;

        $activeOrganizations = factory(Organization::class, $numberOfActiveOrganizations)->create([
            'is_active' => true,
        ]);

        factory(Organization::class, $numberOfInactiveOrganizations)->create([
            'is_active' => false,
        ]);

        $this->assertEquals(
            $numberOfInactiveOrganizations + $numberOfActiveOrganizations,
            Organization::withoutGlobalScopes()->count()
        );

        $response = $this->get(route('organizations.list'));

        $response->assertOk();

        $data = $response->json('data');

        $this->assertCount($numberOfActiveOrganizations, $data);
        $this->assertEquals(
            OrganizationResource::collection($activeOrganizations)->toArray(null),
            $data
        );
    }

    /** @test */
    public function authorized_users_get_correct_information_about_favorite_organizations(): void
    {
        $notFavoritedOrganizations = $this->createOrganizations(3);
        $favoritedOrganizations = $this->createOrganizations(3);

        $user = $this->createUser();
        $anotherUser = $this->createUser();

        $user->favoriteOrganizations()->sync($favoritedOrganizations);
        $anotherUser->favoriteOrganizations()->sync($notFavoritedOrganizations);

        // when guest is fetching
        $response = $this->get(route('organizations.list'));

        $response->assertOk();

        $data = collect($response->json('data'));

        $this->assertCount(6, $data);

        $data->each(fn ($organization) => $this->assertFalse($organization['is_favorited']));

        // when logged in user is fetching
        $this->actingAs($user);

        $response = $this->get(route('organizations.list'));

        $response->assertOk();

        $data = collect($response->json('data'));

        $this->assertCount(6, $data);

        $favoritedIdsFromResponse = $data->where('is_favorited', true)->pluck('id')->toArray();
        $this->assertEquals($favoritedOrganizations->pluck('id')->toArray(), $favoritedIdsFromResponse);

        $notFavoritedIdsFromResponse = $data->where('is_favorited', false)->pluck('id')->toArray();
        $this->assertEquals($notFavoritedOrganizations->pluck('id')->toArray(), $notFavoritedIdsFromResponse);
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

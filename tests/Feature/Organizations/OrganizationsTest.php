<?php

namespace Tests\Feature\Organizations;

use App\Http\Resources\Users\OrganizationResource;
use App\Models\Organization\Organization;
use Illuminate\Http\Response;
use Tests\TestCase;

class OrganizationsTest extends TestCase
{
    /** @test */
    public function users_can_view_organizations(): void
    {
        $numberOfOrganizations = 5;
        $this->createOrganizations($numberOfOrganizations);

        $this->assertCount($numberOfOrganizations, Organization::all());

        $response = $this->get(route('organizations.list'));

        $response->assertOk();

        $data = $response->json('data');

        $this->assertCount(5, $data);
        $this->assertEquals(
            OrganizationResource::collection(Organization::all())->response()->getData(true)['data'],
            $data
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

        $activeOrganizations = $this->createOrganizations($numberOfActiveOrganizations, [
            'is_active' => true,
        ]);

        $this->createOrganizations($numberOfInactiveOrganizations, [
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
            OrganizationResource::collection($activeOrganizations->sortBy('id'))->response()->getData(true)['data'],
            collect($data)->sortBy('id')->toArray()
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

    /** @test */
    public function users_can_see_organizations_that_banned_them(): void
    {
        $organizationThatBannedUser = $this->createOrganization();
        $this->createOrganization();

        $user = $this->createUser();

        $organizationThatBannedUser->bannedUsers()->attach($user->id);

        $this->assertCount(2, Organization::all());

        $this->actingAs($user);

        $response = $this->get(route('organizations.list'));

        $response->assertOk();

        $data = $response->json('data');

        $this->assertCount(2, $data);
    }
}

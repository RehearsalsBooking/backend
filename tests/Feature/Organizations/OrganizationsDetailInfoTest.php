<?php

namespace Tests\Feature\Organizations;

use App\Http\Resources\RoomPriceResource;
use App\Http\Resources\Users\OrganizationDetailResource;
use App\Http\Resources\Users\UserResource;
use Tests\TestCase;

class OrganizationsDetailInfoTest extends TestCase
{
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
    public function user_cannot_see_detailed_info_of_organization_that_banned_them(): void
    {
        $organization = $this->createOrganization();
        $user = $this->createUser();
        $organization->bannedUsers()->attach($user->id);

        $this->assertTrue($organization->isUserBanned($user->id));

        $this->actingAs($user);

        $this->get(route('organizations.show', $organization->id))->assertForbidden();
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
            (new UserResource($owner))->response()->getData(true)['data'],
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

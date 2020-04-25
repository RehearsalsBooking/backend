<?php

namespace Tests\Feature\Organizations;

use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class FavoriteOrganizationsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;

    /** @test */
    public function unauthorized_users_cannot_access_endpoints(): void
    {
        $this->json('post', route('favorite-organizations.create', $this->organization->id))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_responses_with_404_when_user_provided_unknown_organization(): void
    {
        $this->actingAs($this->user);
        $this->json('post', route('favorite-organizations.create', 10000))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json('post', route('favorite-organizations.create', 'unknown'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function user_can_add_organization_to_his_favorite_organizations(): void
    {
        $this->actingAs($this->user);

        $this->assertEquals(0, $this->user->favoriteOrganizations()->count());

        $response = $this->json('post', route('favorite-organizations.create', $this->organization->id));
        $response->assertCreated();

        $this->assertEquals(1, $this->user->favoriteOrganizations()->count());
        $this->assertEquals($this->organization->id, $this->user->favoriteOrganizations->first()->id);
    }

    /** @test */
    public function when_user_adds_organization_to_favorites_when_it_is_already_in_favorites_no_error_is_thrown(): void
    {
        $this->user->favoriteOrganizations()->attach($this->organization->id);
        $this->user->favoriteOrganizations()->attach($this->createOrganization()->id);

        $this->actingAs($this->user);

        $this->assertEquals(2, $this->user->favoriteOrganizations()->count());

        $response = $this->json('post', route('favorite-organizations.create', $this->organization->id));
        $response->assertCreated();

        $this->assertEquals(2, $this->user->favoriteOrganizations()->count());
        $this->assertEquals($this->organization->id, $this->user->favoriteOrganizations->first()->id);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->organization = $this->createOrganization();
    }
}

<?php

namespace Tests\Feature\Management\Rehearsals;

use App\Models\Organization\OrganizationUserBan;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class UnbanUserTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.bans.delete';
    private string $httpVerb = 'delete';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, [1, 1]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        $this->banUsers($this->organization, 1);
        $banToDelete = OrganizationUserBan::first();

        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);

        $this->actingAs($ordinaryClient);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $banToDelete->id])
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $banToDelete->id])
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function deleting_ban_should_be_created_at_given_organization(): void
    {
        $this->banUsers($this->organization, 1);
        $banToDelete = OrganizationUserBan::first();

        $managerOfAnotherOrganization = $this->createUser();
        $anotherOrganization = $this->createOrganizationForUser($managerOfAnotherOrganization);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$anotherOrganization->id, $banToDelete->id])
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_or_ban_is_given(): void
    {
        $this->actingAs($this->manager);

        $this->banUsers($this->organization, 1);
        $ban = OrganizationUserBan::first();

        $this->json($this->httpVerb, route($this->endpoint, [1000, $ban->id]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, ['some text', $ban->id]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, [$this->organization->id, 1000]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, [$this->organization->id, 'some text']))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function manager_of_organization_can_unban_user(): void
    {
        $bannedUsersCount = 3;
        $this->banUsers($this->organization, $bannedUsersCount)->first();
        $banToDelete = OrganizationUserBan::first();
        $this->assertEquals($bannedUsersCount, $this->organization->bannedUsers()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $banToDelete->id])
        );
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals($bannedUsersCount - 1, $this->organization->bannedUsers()->count());
        $this->assertDatabaseMissing('organizations_users_bans', [
            'id' => $banToDelete->id,
        ]);
    }
}

<?php

namespace Tests\Feature\Management\Rehearsals;

use App\Models\Rehearsal;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class BanUserTest extends ManagementTestCase
{
    private string $endpoint = 'management.organization.bans.create';
    private string $httpVerb = 'post';

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
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            [
                'user_id' => $ordinaryClient->id,
                'comment' => 'reason'
            ]
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            [
                'user_id' => $ordinaryClient->id,
                'comment' => 'reason'
            ]
        )
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
    public function it_responds_with_422_when_unknown_user_is_given(): void
    {
        $this->actingAs($this->manager);
        $this->json($this->httpVerb, route($this->endpoint, $this->organization->id), ['user_id' => 10000])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('user_id');
        $this->json($this->httpVerb, route($this->endpoint, $this->organization->id), ['user_id' => 'some id'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('user_id');
    }

    /** @test */
    public function manager_of_organization_can_ban_user(): void
    {
        $this->assertEquals(0, $this->organization->bannedUsers()->count());

        $bannedUser = $this->createUser();

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            [
                'user_id' => $bannedUser->id,
                'comment' => 'reason'
            ]
        );
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, $this->organization->bannedUsers()->count());
        $this->assertDatabaseHas('organizations_users_bans', [
            'organization_id' => $this->organization->id,
            'user_id' => $bannedUser->id,
            'comment' => 'reason'
        ]);
    }

    /** @test */
    public function when_user_is_banned_all_his_future_rehearsals_at_this_organization_are_deleted(): void
    {
        $bannedUser = $this->createUser();
        $rehearsalInFuture = $this->createRehearsalForUserInFuture($bannedUser, $this->organization);
        $rehearsalInPast = $this->createRehearsalForUserInPast($bannedUser, $this->organization);

        $this->assertEquals(
            2,
            Rehearsal::where('organization_id', $this->organization->id)
                ->where('user_id', $bannedUser->id)
                ->count()
        );

        $this->assertEquals(2, $bannedUser->rehearsals()->count());

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, $this->organization->id),
            [
                'user_id' => $bannedUser->id,
                'comment' => 'reason'
            ]
        );
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(
            1,
            Rehearsal::where('organization_id', $this->organization->id)
                ->where('user_id', $bannedUser->id)
                ->count()
        );
        $this->assertEquals(1, $bannedUser->rehearsals()->count());
        $this->assertDatabaseMissing('rehearsals', [
            'id' => $rehearsalInFuture->id,
        ]);
        $this->assertDatabaseMissing('rehearsal_user', [
            'rehearsal_id' => $rehearsalInFuture->id,
        ]);
        $this->assertDatabaseHas('rehearsal_user', [
            'rehearsal_id' => $rehearsalInPast->id,
        ]);
    }
}

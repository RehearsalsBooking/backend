<?php

namespace Tests\Feature\Rehearsals;

use App\Http\Resources\Management\RehearsalDetailedResource;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ShowRehearsalTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = 'rehearsals.show';
    private string $httpVerb = 'get';
    private Rehearsal $rehearsal;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->rehearsal = $this->createRehearsal(
            1,
            2,
            $this->createOrganization(),
            $this->createBandForUser($this->user),
            true,
            $this->user
        );
    }

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, $this->rehearsal->id))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_user_is_forbidden_from_fetching_full_info(): void
    {
        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);

        $otherBandMember = $this->createUser();
        $this->createBandForUser($otherBandMember);

        $unauthorizedUsers = [
            $managerOfAnotherOrganization,
            $otherBandMember,
            $this->createUser()
        ];

        foreach ($unauthorizedUsers as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser);
            $this->json($this->httpVerb, route($this->endpoint, $this->rehearsal->id))
                ->assertStatus(Response::HTTP_FORBIDDEN);
        }
    }

    /** @test */
    public function it_responds_with_404_when_unknown_rehearsal_is_given(): void
    {
        $this->actingAs($this->user);
        $this->json($this->httpVerb, route($this->endpoint, 1000))
            ->assertNotFound();
        $this->json($this->httpVerb, route($this->endpoint, 'some text'))
            ->assertNotFound();
    }

    /** @test */
    public function user_who_booked_or_any_band_member_or_manager_of_organization_can_fetch_rehearsal_info(): void
    {
        $anotherBandMember = $this->createUser();
        $this->rehearsal->band->addMember($anotherBandMember->id);

        $authorizedUsers = [
            $this->user,
            $this->rehearsal->organization->owner,
            $this->rehearsal->band->admin,
            $anotherBandMember
        ];

        foreach ($authorizedUsers as $authorizedUser) {
            $this->actingAs($authorizedUser);

            $response = $this->json(
                $this->httpVerb,
                route($this->endpoint, $this->rehearsal->id)
            );
            $response->assertStatus(Response::HTTP_OK);

            $data = $response->json('data');

            $this->assertEquals(
                (new RehearsalDetailedResource($this->rehearsal))->response()->getData(true)['data'],
                $data
            );
        }
    }
}

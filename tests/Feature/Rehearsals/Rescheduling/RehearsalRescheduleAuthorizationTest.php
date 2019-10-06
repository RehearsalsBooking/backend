<?php

namespace Tests\Feature\Rehearsals\Rescheduling;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalRescheduleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthorized_user_cannot_reschedule_a_rehearsal(): void
    {
        $response = $this->json('put', route('organizations.rehearsals.reschedule', [1, 1]));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_admin_of_a_band_can_reschedule_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $band = $this->createBandForUser($this->createUser());

        $rehearsal = $this->createRehearsal($organization, 10, 12, $band);

        $user = $this->createUser();
        $this->actingAs($user);

        $this->json('put', route('organizations.rehearsals.reschedule', [$organization->id, $rehearsal->id]), [
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00)
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->json('put', route('organizations.rehearsals.reschedule', [$organization->id, $rehearsal->id]), [
            'band_id' => $band->id,
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00)
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function user_can_reschedule_only_his_own_individual_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $rehearsalsOwner = $this->createUser();

        $rehearsal = $this->createRehearsal($organization, 10, 12, null, false, $rehearsalsOwner);

        $someOtherUser = $this->createUser();
        $this->actingAs($someOtherUser);

        $this->json('put', route('organizations.rehearsals.reschedule', [$organization->id, $rehearsal->id]), [
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00)
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

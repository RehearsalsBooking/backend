<?php

namespace Tests\Feature\Rehearsals\Rescheduling;

use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalRescheduleAuthorizationTest extends TestCase
{
    /** @test */
    public function unauthorized_user_cannot_reschedule_a_rehearsal(): void
    {
        $response = $this->json('put', route('rehearsals.reschedule', 1));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_admin_of_a_band_can_reschedule_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $band = $this->createBandForUser($this->createUser());

        $rehearsal = $this->createRehearsal(10, 12, $room, $band);

        $user = $this->createUser();
        $this->actingAs($user);

        $this->json('put', route('rehearsals.reschedule', $rehearsal->id), [
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00),
        ])
            ->assertForbidden();

        $this->json('put', route('rehearsals.reschedule', $rehearsal->id), [
            'band_id' => $band->id,
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00),
        ])
            ->assertForbidden();
    }

    /** @test */
    public function user_can_reschedule_only_his_own_individual_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $rehearsalsOwner = $this->createUser();

        $rehearsal = $this->createRehearsal(10, 12, $room, null, false, $rehearsalsOwner);

        $someOtherUser = $this->createUser();
        $this->actingAs($someOtherUser);

        $this->json('put', route('rehearsals.reschedule', $rehearsal->id), [
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00),
        ])
            ->assertForbidden();
    }
}

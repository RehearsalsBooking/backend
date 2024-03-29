<?php

namespace Tests\Feature\Rehearsals\Booking;

use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalBookingAuthorizationTest extends TestCase
{
    /** @test */
    public function unauthorized_user_cannot_book_a_rehearsal(): void
    {
        $response = $this->json('post', route('rehearsals.create'));

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_admin_of_a_band_can_book_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $band = $this->createBandForUser($this->createUser());

        $user = $this->createUser();
        $this->actingAs($user);

        $this->json('post', route('rehearsals.create'), [
            'organization_room_id' => $room->id,
            'band_id' => $band->id,
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00),
        ])
            ->assertForbidden();
    }
}

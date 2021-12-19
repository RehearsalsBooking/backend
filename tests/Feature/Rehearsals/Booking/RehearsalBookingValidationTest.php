<?php

namespace Tests\Feature\Rehearsals\Booking;

use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalBookingValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createUser());
    }

    /** @test */
    public function it_responds_with_422_when_user_provided_unknown_organization_room_id(): void
    {
        $this->json('post', route('rehearsals.create'), [
            'organization_room_id' => 10000,
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('organization_room_id');

        $this->json('post', route('rehearsals.create'), [
            'organization_room_id' => 'asd',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('organization_room_id');
    }

    /** @test */
    public function it_responds_with_validation_error_when_user_provided_unknown_band_id(): void
    {
        $organization = $this->createOrganization();

        $this->json('post', route('rehearsals.create'), [
            'organization_room_id' => $organization->id,
            'band_id' => 10000,
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00),
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('band_id');
    }
}

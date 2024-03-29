<?php

namespace Tests\Feature\Rehearsals\Rescheduling;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Tests\TestCase;

class RehearsalRescheduleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Rehearsal::truncate();
    }

    /** @test */
    public function user_can_reschedule_his_individual_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);
        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(10, 12, $room, null, false, $user);

        $this->assertEquals(1, Rehearsal::count());

        $newRehearsalStartTime = $rehearsal->time->from()->addHours(2);
        $newRehearsalEndTime = $rehearsal->time->to()->addHours(2);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString(),
            ]
        );

        $response->assertOk();

        $this->assertEquals(1, Rehearsal::count());

        $rescheduledRehearsal = Rehearsal::first();
        $this->assertEquals($newRehearsalStartTime, $rescheduledRehearsal->time->from()->toDateTimeString());
        $this->assertEquals($newRehearsalEndTime, $rescheduledRehearsal->time->to()->toDateTimeString());
        $this->assertEquals(
            (new RehearsalResource($rescheduledRehearsal))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function user_can_reschedule_rehearsal_on_behalf_of_his_band(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);
        $user = $this->createUser();

        $band = $this->createBandForUser($user);

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(10, 12, $room, $band, false, $user);

        $this->assertEquals(1, Rehearsal::count());

        $newRehearsalStartTime = $rehearsal->time->from()->addHours(2);
        $newRehearsalEndTime = $rehearsal->time->to()->addHours(2);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString(),
            ]
        );

        $response->assertOk();

        $this->assertEquals(1, Rehearsal::count());

        $rescheduledRehearsal = Rehearsal::first();
        $this->assertEquals($newRehearsalStartTime, $rescheduledRehearsal->time->from()->toDateTimeString());
        $this->assertEquals($newRehearsalEndTime, $rescheduledRehearsal->time->to()->toDateTimeString());
        $this->assertEquals(
            (new RehearsalResource($rescheduledRehearsal))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function when_user_reschedules_rehearsal_its_payment_status_is_not_changed(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);


        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(
            room: $room,
            isPaid: true,
            user: $user
        );

        $this->assertTrue($rehearsal->is_paid);

        $newRehearsalStartTime = $rehearsal->time->from()->addHours(2);
        $newRehearsalEndTime = $rehearsal->time->to()->addHours(2);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString(),
            ]
        );

        $response->assertOk();

        $createdRehearsal = Rehearsal::first();

        $this->assertTrue($createdRehearsal->is_paid);
    }
}

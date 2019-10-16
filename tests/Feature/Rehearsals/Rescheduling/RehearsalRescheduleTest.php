<?php

namespace Tests\Feature\Rehearsals\Rescheduling;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalRescheduleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_reschedule_his_individual_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal($organization, 10, 12, null, false, $user);

        $this->assertEquals(1, Rehearsal::count());

        $newRehearsalStartTime = $rehearsal->starts_at->addHours(2);
        $newRehearsalEndTime = $rehearsal->ends_at->addHours(2);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString()
            ]
        );

        $response->assertOk();

        $this->assertEquals(1, Rehearsal::count());

        $rescheduledRehearsal = Rehearsal::first();
        $this->assertEquals($newRehearsalStartTime, $rescheduledRehearsal->starts_at->toDateTimeString());
        $this->assertEquals($newRehearsalEndTime, $rescheduledRehearsal->ends_at->toDateTimeString());
        $this->assertEquals(
            (new RehearsalResource($rescheduledRehearsal))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function user_can_reschedule_rehearsal_on_behalf_of_his_band(): void
    {
        $organization = $this->createOrganization();
        $user = $this->createUser();

        $band = $this->createBandForUser($user);

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal($organization, 10, 12, $band, false, $user);

        $this->assertEquals(1, Rehearsal::count());

        $newRehearsalStartTime = $rehearsal->starts_at->addHours(2);
        $newRehearsalEndTime = $rehearsal->ends_at->addHours(2);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString()
            ]
        );

        $response->assertOk();

        $this->assertEquals(1, Rehearsal::count());

        $rescheduledRehearsal = Rehearsal::first();
        $this->assertEquals($newRehearsalStartTime, $rescheduledRehearsal->starts_at->toDateTimeString());
        $this->assertEquals($newRehearsalEndTime, $rescheduledRehearsal->ends_at->toDateTimeString());
        $this->assertEquals(
            (new RehearsalResource($rescheduledRehearsal))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function when_user_reschedules_rehearsal_its_status_is_set_to_unconfirmed(): void
    {
        $organization = $this->createOrganization();

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal($organization, 10, 12, null, true, $user);

        $newRehearsalStartTime = $rehearsal->starts_at->addHours(2);
        $newRehearsalEndTime = $rehearsal->ends_at->addHours(2);

        $response = $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString()
            ]
        );

        $response->assertOk();

        $createdRehearsal = Rehearsal::first();

        $this->assertFalse($createdRehearsal->is_confirmed);
    }

}
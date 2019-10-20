<?php

namespace Tests\Unit\Rehearsals;

use App\Models\Band;
use App\Models\Organization;
use App\Models\Rehearsal;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function rehearsal_has_one_organization(): void
    {
        $organization = $this->createOrganization();

        $rehearsal = factory(Rehearsal::class)->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $rehearsal->organization);
    }

    /** @test */
    public function rehearsal_has_user_who_booked_this_rehearsal(): void
    {
        $user = $this->createUser();
        $rehearsal = factory(Rehearsal::class)->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $rehearsal->user);
    }

    /** @test */
    public function rehearsal_can_be_booked_by_band(): void
    {
        $user = $this->createUser();
        $band = $this->createBandForUser($user);

        /** @var Rehearsal $rehearsal */
        $rehearsal = factory(Rehearsal::class)->create([
            'user_id' => $user->id,
            'band_id' => $band->id,
        ]);

        $this->assertInstanceOf(
            Band::class,
            $rehearsal->band
        );

        $this->assertEquals(
            $band->id,
            $rehearsal->band->id
        );
    }

    /** @test */
    public function rehearsal_has_many_attendees(): void
    {
        $rehearsal = factory(Rehearsal::class)->create();

        $attendeesCount = 5;
        $attendees = factory(User::class, $attendeesCount)->create()->each(static function ($attendee) use ($rehearsal) {
            \DB::table('rehearsal_user')
                ->insert([
                    'rehearsal_id' => $rehearsal->id,
                    'user_id' => $attendee->id
                ]);
        });

        $this->assertEquals($attendeesCount, $rehearsal->attendees()->count());
        $this->assertInstanceOf(User::class, $rehearsal->attendees->first());
        $this->assertEquals($attendees->pluck('id'), $rehearsal->attendees->pluck('id'));
    }
}

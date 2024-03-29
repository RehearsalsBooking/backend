<?php

namespace Tests\Unit\Rehearsals;

use App\Models\Band;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RehearsalsTest extends TestCase
{
    /** @test */
    public function rehearsal_has_one_room(): void
    {
        $room = $this->createOrganizationRoom();

        $rehearsal = $this->createRehearsalsForRoom($room)->first();

        $this->assertInstanceOf(OrganizationRoom::class, $rehearsal->room);
        $this->assertEquals($room->id, $rehearsal->room->id);
    }

    /** @test */
    public function rehearsal_has_user_who_booked_this_rehearsal(): void
    {
        $user = $this->createUser();
        $rehearsal = Rehearsal::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $rehearsal->user);
    }

    /** @test */
    public function rehearsal_can_be_booked_by_band(): void
    {
        $user = $this->createUser();
        $band = $this->createBandForUser($user);

        $rehearsal = $this->createRehearsalForBandInFuture($band, $user);

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
        Event::fake();
        $rehearsal = Rehearsal::factory()->create();
        $attendeesCount = 5;
        $attendees = User::factory()->count($attendeesCount)->create()->each(static function ($attendee) use ($rehearsal
        ) {
            DB::table('rehearsal_user')
                ->insert([
                    'rehearsal_id' => $rehearsal->id,
                    'user_id' => $attendee->id,
                ]);
        });

        $this->assertEquals($attendeesCount, $rehearsal->attendees()->count());
        $this->assertInstanceOf(User::class, $rehearsal->attendees->first());

        $expectedAttendeesIds = $attendees->pluck('id')->toArray();
        $actualAttendeesIds = $rehearsal->attendees->pluck('id')->toArray();
        $this->assertEquals(
            sort($expectedAttendeesIds),
            sort($actualAttendeesIds)
        );
    }

    /** @test */
    public function when_individual_rehearsal_is_created_user_becomes_its_attendee(): void
    {
        $user = $this->createUser();
        $rehearsal = $this->createRehearsalForUser($user);

        $this->assertEquals(1, $rehearsal->attendees()->count());
        $this->assertContains($user->id, $rehearsal->attendees()->pluck('id'));
    }

    /** @test */
    public function when_band_rehearsal_is_created_band_members_become_its_attendee(): void
    {
        $user = $this->createUser();
        $band = $this->createBandForUser($user);

        $bandMembers = $this->createUsers(4);

        $bandMembers->each(function (User $user) use ($band) {
            $band->addMember($user->id);
        });

        $bandMembers->push($user);

        $rehearsal = $this->createRehearsalForBandInFuture($band);

        $this->assertEquals(5, $rehearsal->attendees()->count());
        $expectedMemberIds = array_values($bandMembers->pluck('id')->sort()->toArray());
        $actualMemberIds = array_values($rehearsal->attendees->pluck('id')->sort()->toArray());
        $this->assertEquals($expectedMemberIds, $actualMemberIds);
    }
}

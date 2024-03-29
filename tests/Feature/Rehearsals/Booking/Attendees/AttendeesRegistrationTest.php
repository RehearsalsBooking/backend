<?php

namespace Tests\Feature\Rehearsals\Booking\Attendees;

use App\Models\Band;
use App\Models\BandMembership;
use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AttendeesRegistrationTest extends TestCase
{
    /** @test */
    public function when_user_books_and_reschedules_individual_rehearsal_he_becomes_this_rehearsals_attendee(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);

        $this->assertEquals(0, $user->rehearsals()->count());
        $this->assertEquals(0, Rehearsal::count());

        $this->bookRehearsal($room);

        $this->assertEquals(1, $user->rehearsals()->count());
        $this->assertEquals(1, Rehearsal::count());

        $rehearsal = Rehearsal::first();
        $this->assertEquals(1, $rehearsal->attendees()->count());
        $this->assertEquals($user->id, $rehearsal->attendees->first()->id);

        //test reschedule
        $this->rescheduleRehearsal($rehearsal)->assertOk();

        $rehearsal = Rehearsal::first();

        $this->assertEquals(1, $rehearsal->attendees()->count());
        $this->assertEquals($user->id, $rehearsal->attendees->first()->id);
    }

    protected function bookRehearsal(OrganizationRoom $room, Band $band = null): void
    {
        $parameters = $this->getRehearsalTime();
        $parameters['organization_room_id'] = $room->id;

        if ($band !== null) {
            $parameters['band_id'] = $band->id;
        }

        $this->json(
            'post',
            route('rehearsals.create'),
            $parameters
        )->assertCreated();
    }

    /**
     * @param  Rehearsal  $rehearsal
     * @param  Band|null  $band
     * @return TestResponse
     */
    protected function rescheduleRehearsal(Rehearsal $rehearsal, Band $band = null): TestResponse
    {
        $parameters = [
            'starts_at' => $rehearsal->time->from()->addHour()->toDateTimeString(),
            'ends_at' => $rehearsal->time->to()->addHour()->toDateTimeString(),
        ];

        if ($band !== null) {
            $parameters['band_id'] = $band->id;
        }

        return $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            $parameters
        );
    }

    /** @test */
    public function when_user_books_and_reschedules_rehearsal_for_band_then_all_active_band_members_become_this_rehearsals_attendees(
    ): void
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);

        $band = $this->createBandForUser($user);

        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount - 1);

        $inactiveBandMember = $this->createUser();
        $band->addMember($inactiveBandMember->id);
        BandMembership::where('user_id', $inactiveBandMember->id)->delete();

        $bandMembers->each(function (User $user) use ($band) {
            $band->addMember($user->id);
        });
        $bandMembers->push($user);
        $this->assertEquals($bandMembersCount, $band->fresh()->members()->count());

        $this->assertEquals(0, $user->rehearsals()->count());
        $this->assertEquals(0, Rehearsal::count());

        $this->bookRehearsal($room, $band);

        $this->assertEquals(1, Rehearsal::count());
        $rehearsal = Rehearsal::first();

        $bandMembers->each(function (User $bandMember) use ($rehearsal) {
            $this->assertEquals(1, $bandMember->rehearsals()->count());
            $this->assertEquals($rehearsal->id, $bandMember->rehearsals->first()->id);
        });
        $this->assertEquals(0, $inactiveBandMember->rehearsals()->count());

        $this->assertEquals($bandMembersCount, $rehearsal->attendees()->count());
        $this->assertEquals(
            $bandMembers->sortBy('id')->pluck('id'),
            $rehearsal->attendees->sortBy('id')->pluck('id')
        );

        //test reschedule
        $this->rescheduleRehearsal($rehearsal, $band)->assertOk();

        $rehearsal = Rehearsal::first();

        $bandMembers->each(function (User $bandMember) use ($rehearsal) {
            $this->assertEquals(1, $bandMember->rehearsals()->count());
            $this->assertEquals($rehearsal->id, $bandMember->rehearsals->first()->id);
        });

        $this->assertEquals($bandMembersCount, $rehearsal->attendees()->count());
        $expectedBandMembersIds = $bandMembers->pluck('id')->toArray();
        $actualBandMembersArray = $rehearsal->attendees->pluck('id')->toArray();
        $this->assertEquals(
            asort($expectedBandMembersIds),
            asort($actualBandMembersArray)
        );
    }

    /** @test */
    public function when_rehearsal_is_deleted_its_attendees_are_also_deleted(): void
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);
        $this->createPricesForOrganization($organization);

        $band = $this->createBandForUser($user);

        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount - 1);

        $bandMembers->each(function (User $user) use ($band) {
            $band->addMember($user->id);
        });

        $bandMembers->push($user);

        $this->bookRehearsal($room, $band);

        $this->assertEquals(1, Rehearsal::count());
        $rehearsal = Rehearsal::first();

        $this->assertEquals($bandMembersCount, $rehearsal->attendees()->count());

        $this->delete(route('rehearsals.delete', $rehearsal->id));

        $this->assertEquals(0, Rehearsal::count());
        $bandMembers->each(function (User $bandMember) {
            $this->assertEquals(0, $bandMember->rehearsals()->count());
        });
    }
}

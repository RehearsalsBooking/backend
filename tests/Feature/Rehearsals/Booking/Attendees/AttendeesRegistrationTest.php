<?php

namespace Tests\Feature\Rehearsals\Booking\Attendees;

use App\Models\Band;
use App\Models\Organization;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class AttendeesRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function when_user_books_and_reschedules_individual_rehearsal_he_becomes_this_rehearsals_attendee(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $organization = $this->createOrganization();

        $this->assertEquals(0, $user->rehearsals()->count());
        $this->assertEquals(0, Rehearsal::count());

        $this->bookRehearsal($organization);

        $this->assertEquals(1, $user->rehearsals()->count());
        $this->assertEquals(1, Rehearsal::count());

        $rehearsal = Rehearsal::first();
        $this->assertEquals(1, $rehearsal->attendees()->count());
        $this->assertEquals($user->id, $rehearsal->attendees->first()->id);

        //test reschedule
        $this->rescheduleRehearsal($organization, $rehearsal)->assertOk();

        $rehearsal = Rehearsal::first();

        $this->assertEquals(1, $rehearsal->attendees()->count());
        $this->assertEquals($user->id, $rehearsal->attendees->first()->id);
    }

    /** @test */
    public function when_user_books_and_reschedules_rehearsal_for_band_then_all_band_members_become_this_rehearsals_attendees(): void
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $organization = $this->createOrganization();

        $band = $this->createBandForUser($user);

        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount - 1)->merge([$user]);

        $band->members()->attach($bandMembers);

        $this->assertEquals(0, $user->rehearsals()->count());
        $this->assertEquals(0, Rehearsal::count());

        $this->bookRehearsal($organization, $band);

        $this->assertEquals(1, Rehearsal::count());
        $rehearsal = Rehearsal::first();

        $bandMembers->each(function (User $bandMember) use ($rehearsal) {
            $this->assertEquals(1, $bandMember->rehearsals()->count());
            $this->assertEquals($rehearsal->id, $bandMember->rehearsals->first()->id);
        });

        $this->assertEquals($bandMembersCount, $rehearsal->attendees()->count());
        $this->assertEquals($bandMembers->pluck('id'), $rehearsal->attendees->pluck('id'));

        //test reschedule
        $this->rescheduleRehearsal($organization, $rehearsal, $band)->assertOk();

        $rehearsal = Rehearsal::first();

        $bandMembers->each(function (User $bandMember) use ($rehearsal) {
            $this->assertEquals(1, $bandMember->rehearsals()->count());
            $this->assertEquals($rehearsal->id, $bandMember->rehearsals->first()->id);
        });

        $this->assertEquals($bandMembersCount, $rehearsal->attendees()->count());
        $this->assertEquals($bandMembers->pluck('id'), $rehearsal->attendees->pluck('id'));
    }

    /** @test */
    public function when_rehearsal_is_deleted_its_attendees_are_also_deleted(): void
    {
        $user = $this->createUser();

        $this->actingAs($user);

        $organization = $this->createOrganization();

        $band = $this->createBandForUser($user);

        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount - 1)->merge([$user]);

        $band->members()->attach($bandMembers);

        $this->bookRehearsal($organization, $band);

        $this->assertEquals(1, Rehearsal::count());
        $rehearsal = Rehearsal::first();

        $this->assertEquals($bandMembersCount, $rehearsal->attendees()->count());

        $this->delete(route('rehearsals.delete', $rehearsal->id));

        $this->assertEquals(0, Rehearsal::count());
        $bandMembers->each(function (User $bandMember) {
            $this->assertEquals(0, $bandMember->rehearsals()->count());
        });
    }

    /**
     * @param Organization $organization
     * @param Band|null $band
     * @return void
     */
    protected function bookRehearsal(Organization $organization, Band $band = null): void
    {
        $parameters = $this->getRehearsalTime();
        $parameters['organization_id'] = $organization->id;

        if ($band) {
            $parameters['band_id'] = $band->id;
        }

        $this->json(
            'post',
            route('rehearsals.create'),
            $parameters
        );
    }

    /**
     * @param Organization $organization
     * @param Rehearsal $rehearsal
     * @param Band|null $band
     * @return TestResponse
     */
    protected function rescheduleRehearsal(Organization $organization, Rehearsal $rehearsal, Band $band = null): TestResponse
    {
        $parameters = [
            'starts_at' => $rehearsal->starts_at->addHour()->toDateTimeString(),
            'ends_at' => $rehearsal->ends_at->addHour()->toDateTimeString()
        ];

        if ($band) {
            $parameters['band_id'] = $band->id;
        }

        return $this->json(
            'put',
            route('rehearsals.reschedule', $rehearsal->id),
            $parameters
        );
    }
}
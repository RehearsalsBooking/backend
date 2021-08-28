<?php

namespace Tests\Feature\Bands\Members;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class BandMembersDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $bandAdmin;
    private Band $band;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bandAdmin = $this->createUser();
        $this->band = $this->createBandForUser($this->bandAdmin);
    }

    /** @test */
    public function band_admin_can_remove_bands_members(): void
    {
        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount);
        foreach ($bandMembers as $bandMember) {
            $this->band->addMember($bandMember->id);
        }

        $this->actingAs($this->bandAdmin);

        $this->assertEquals($bandMembersCount, $this->band->memberships()->count());
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $this->band->memberships()->pluck('user_id')->toArray()
        );

        $bandMembershipIdToDelete = $this->band->memberships()->inRandomOrder()->first(['id'])->id;

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $bandMembershipIdToDelete]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals($bandMembersCount - 1, $this->band->memberships()->count());
        $this->assertNotContains(
            $bandMembershipIdToDelete,
            $this->band->memberships()->pluck('id')->toArray()
        );
    }

    /** @test */
    public function user_can_leave_band(): void
    {
        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount);
        $bandMembers->each(function (User $user) {
            $this->band->addMember($user->id);
        });

        $this->assertEquals($bandMembersCount, $this->band->members()->count());
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $this->band->memberships()->pluck('user_id')->toArray()
        );

        $membershipOfUserWhoIsLeavingBand = $this->band->memberships()->inRandomOrder()->first();

        $this->actingAs($membershipOfUserWhoIsLeavingBand->user);

        $response = $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $membershipOfUserWhoIsLeavingBand->id])
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals($bandMembersCount - 1, $this->band->members()->count());
        $this->assertNotContains(
            $membershipOfUserWhoIsLeavingBand->id,
            $this->band->memberships()->pluck('id')->toArray()
        );
    }

    /** @test */
    public function when_user_leaves_band_he_is_no_longer_attendee_of_this_bands_future_rehearsals(): void
    {
        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount);

        $someOtherBand = $this->createBand();
        $bandMembers->each(function (User $user) {
            $this->band->addMember($user->id);
        });
        $bandMembers->each(function (User $user) use ($someOtherBand) {
            $someOtherBand->addMember($user->id);
        });

        $rehearsalInPast = $this->createRehearsalForBandInThePast($this->band);
        $rehearsalInPastForOtherBand = $this->createRehearsalForBandInThePast($someOtherBand);

        $rehearsalInFuture = $this->createRehearsalForBandInFuture($this->band);
        $rehearsalInFutureForOtherBand = $this->createRehearsalForBandInFuture($someOtherBand);

        $this->assertEquals(
            $bandMembers->sortBy('id')->pluck('id')->toArray(),
            $rehearsalInPast->attendees->sortBy('id')->pluck('id')->toArray()
        );
        $this->assertEquals(
            $bandMembers->sortBy('id')->pluck('id')->toArray(),
            $rehearsalInFuture->attendees->sortBy('id')->pluck('id')->toArray()
        );
        $this->assertEquals(
            $bandMembers->sortBy('id')->pluck('id')->toArray(),
            $rehearsalInFutureForOtherBand->attendees->sortBy('id')->pluck('id')->toArray()
        );
        $this->assertEquals(
            $bandMembers->sortBy('id')->pluck('id')->toArray(),
            $rehearsalInFutureForOtherBand->attendees->sortBy('id')->pluck('id')->toArray()
        );

        $membershipOfUserWhoIsLeavingBand = $this->band->memberships()->inRandomOrder()->first();

        $this->actingAs($membershipOfUserWhoIsLeavingBand->user);

        $response = $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $membershipOfUserWhoIsLeavingBand->id])
        );
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals(
            $bandMembersCount,
            $rehearsalInPast->fresh(['attendees'])->attendees()->count()
        );
        $this->assertEquals(
            $bandMembers->sortBy('id')->pluck('id')->toArray(),
            $rehearsalInPast->fresh(['attendees'])->attendees->sortBy('id')->pluck('id')->toArray()
        );
        $this->assertEquals(
            $bandMembersCount,
            $rehearsalInPastForOtherBand->fresh(['attendees'])->attendees()->count()
        );
        $this->assertEquals(
            $bandMembers->sortBy('id')->pluck('id')->toArray(),
            $rehearsalInPastForOtherBand->fresh(['attendees'])
                ->attendees
                ->sortBy('id')
                ->pluck('id')
                ->toArray()
        );

        $this->assertEquals(
            $bandMembersCount - 1,
            $rehearsalInFuture->fresh(['attendees'])->attendees()->count()
        );
        $this->assertEquals(
            $bandMembersCount,
            $rehearsalInFutureForOtherBand->fresh(['attendees'])->attendees()->count()
        );
        $this->assertEquals(
            $this->band->fresh(['members'])->members->sortBy('id')->pluck('id')->toArray(),
            $rehearsalInFuture->fresh(['attendees'])->attendees->sortBy('id')->pluck('id')->toArray()
        );
        $this->assertNotContains(
            $membershipOfUserWhoIsLeavingBand->user_id,
            $rehearsalInFuture->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );
    }
}

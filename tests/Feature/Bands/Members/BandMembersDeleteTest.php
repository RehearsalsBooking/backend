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
    public function only_band_admin_can_delete_member(): void
    {
        $bandMember = $this->createUser();
        $this->band->members()->attach($bandMember->id);
        $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $bandMember->id])
        )->assertUnauthorized();
        $this->actingAs($this->createUser())->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $bandMember->id])
        )->assertForbidden();
    }

    /** @test */
    public function band_admin_can_remove_bands_members(): void
    {
        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount);
        $this->band->members()->saveMany($bandMembers);

        $this->actingAs($this->bandAdmin);

        $this->assertEquals($bandMembersCount, $this->band->members()->count());
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $this->band->members->pluck('id')->toArray()
        );

        $userIdToRemoveFromBand = $this->band->members()->inRandomOrder()->first(['id'])->id;

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $userIdToRemoveFromBand]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals($bandMembersCount - 1, $this->band->members()->count());
        $this->assertNotContains(
            $userIdToRemoveFromBand,
            $this->band->fresh(['members'])->pluck('id')->toArray()
        );
    }

    /** @test */
    public function user_can_leave_band(): void
    {
        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount);
        $this->band->members()->saveMany($bandMembers);

        $this->assertEquals($bandMembersCount, $this->band->members()->count());
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $this->band->members->pluck('id')->toArray()
        );

        $userWhoIsLeavingBand = $this->band->members()->inRandomOrder()->first(['id']);

        $this->actingAs($userWhoIsLeavingBand);

        $response = $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $userWhoIsLeavingBand->id])
        );

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals($bandMembersCount - 1, $this->band->members()->count());
        $this->assertNotContains(
            $userWhoIsLeavingBand->id,
            $this->band->fresh(['members'])->pluck('id')->toArray()
        );
    }

    /** @test */
    public function when_user_leaves_band_he_is_no_longer_attendee_of_this_bands_future_rehearsals(): void
    {
        $bandMembersCount = 5;
        $bandMembers = $this->createUsers($bandMembersCount);

        $someOtherBand = $this->createBand();
        $this->band->members()->saveMany($bandMembers);
        $someOtherBand->members()->saveMany($bandMembers);

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

        $userWhoIsLeavingBand = $this->band->members()->inRandomOrder()->first(['id']);

        $this->actingAs($userWhoIsLeavingBand);

        $response = $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $userWhoIsLeavingBand->id])
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
            $userWhoIsLeavingBand->id,
            $rehearsalInFuture->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );
    }

    /** @test */
    public function admin_of_band_cannot_leave_or_be_removed_from_his_band(): void
    {
        $this->band->members()->attach($this->bandAdmin);

        $this->assertEquals(1, $this->band->members()->count());
        $this->assertEquals(
            $this->bandAdmin->id,
            $this->band->members->first()->id
        );

        $this->actingAs($this->bandAdmin);

        $response = $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $this->bandAdmin->id])
        );

        $response->assertForbidden();
    }
}

<?php

namespace Tests\Feature\Bands\Members;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class BandsRegistrationTest.
 * @property User $bandAdmin
 * @property Band $band
 */
class BandMembersDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
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
        $this->band->members()->saveMany($bandMembers);

        $this->actingAs($this->bandAdmin);

        self::assertEquals($bandMembersCount, $this->band->members()->count());
        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $this->band->members->pluck('id')->toArray()
        );

        $userIdToRemoveFromBand = $this->band->members()->inRandomOrder()->first(['id'])->id;

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $userIdToRemoveFromBand]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        self::assertEquals($bandMembersCount - 1, $this->band->members()->count());
        self::assertNotContains(
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

        self::assertEquals($bandMembersCount, $this->band->members()->count());
        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $this->band->members->pluck('id')->toArray()
        );

        $userWhoIsLeavingBand = $this->band->members()->inRandomOrder()->first(['id']);

        $this->actingAs($userWhoIsLeavingBand);

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $userWhoIsLeavingBand->id]));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        self::assertEquals($bandMembersCount - 1, $this->band->members()->count());
        self::assertNotContains(
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

        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInPast->attendees->pluck('id')->toArray()
        );
        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInFuture->attendees->pluck('id')->toArray()
        );
        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInFutureForOtherBand->attendees->pluck('id')->toArray()
        );
        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInFutureForOtherBand->attendees->pluck('id')->toArray()
        );

        $userWhoIsLeavingBand = $this->band->members()->inRandomOrder()->first(['id']);

        $this->actingAs($userWhoIsLeavingBand);

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $userWhoIsLeavingBand->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        self::assertEquals(
            $bandMembersCount,
            $rehearsalInPast->fresh(['attendees'])->attendees()->count()
        );
        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInPast->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );
        self::assertEquals(
            $bandMembersCount,
            $rehearsalInPastForOtherBand->fresh(['attendees'])->attendees()->count()
        );
        self::assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInPastForOtherBand->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );

        self::assertEquals(
            $bandMembersCount - 1,
            $rehearsalInFuture->fresh(['attendees'])->attendees()->count()
        );
        self::assertEquals(
            $bandMembersCount,
            $rehearsalInFutureForOtherBand->fresh(['attendees'])->attendees()->count()
        );
        self::assertEquals(
            $this->band->fresh(['members'])->members->pluck('id')->toArray(),
            $rehearsalInFuture->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );
        self::assertNotContains(
            $userWhoIsLeavingBand->id,
            $rehearsalInFuture->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );
    }
}

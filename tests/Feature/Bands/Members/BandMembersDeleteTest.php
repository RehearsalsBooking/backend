<?php

namespace Tests\Feature\Bands;

use App\Models\Band;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class BandsRegistrationTest
 * @property User $bandAdmin
 * @property Band $band
 * @package Tests\Feature\Bands
 */
class BandMembersDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;
    /**
     * @var Band
     */
    private $band;

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

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $userWhoIsLeavingBand->id]));

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
        $this->band->members()->saveMany($bandMembers);

        $rehearsalInPast = $this->createRehearsalForBandInThePast($this->band);
        $rehearsalInPast->registerBandMembersAsAttendees();

        $rehearsalInFuture = $this->createRehearsalForBandInFuture($this->band);
        $rehearsalInFuture->registerBandMembersAsAttendees();

        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInPast->attendees->pluck('id')->toArray()
        );
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInFuture->attendees->pluck('id')->toArray()
        );

        $userWhoIsLeavingBand = $this->band->members()->inRandomOrder()->first(['id']);

        $this->actingAs($userWhoIsLeavingBand);

        $response = $this->json('delete', route('bands.members.delete', [$this->band->id, $userWhoIsLeavingBand->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals(
            $bandMembersCount,
            $rehearsalInPast->fresh(['attendees'])->attendees()->count()
        );
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInPast->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );

        $this->assertEquals(
            $bandMembersCount - 1,
            $rehearsalInFuture->fresh(['attendees'])->attendees()->count()
        );
        $this->assertEquals(
            $this->band->fresh(['members'])->members->pluck('id')->toArray(),
            $rehearsalInFuture->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );
        $this->assertNotContains(
            $userWhoIsLeavingBand->id,
            $rehearsalInFuture->fresh(['attendees'])->attendees->pluck('id')->toArray()
        );
    }
}

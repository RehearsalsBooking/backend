<?php

namespace Tests\Feature\Bands;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class BandsUpdateTest
 * @property User $bandOwner
 * @package Tests\Feature\Bands
 */
class BandsDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Band
     */
    private $band;

    /**
     * @var User
     */
    private $bandOwner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bandOwner = factory(User::class)->create();
        $this->band = factory(Band::class)->create([
            'admin_id' => $this->bandOwner->id
        ]);

    }

    /** @test */
    public function unauthenticated_user_cannot_delete_band(): void
    {
        $this->json('delete', route('bands.delete', $this->band))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function only_admin_of_a_band_can_delete_it(): void
    {
        $this->actingAs($this->createUser());

        $this->json('delete', route('bands.delete', $this->band))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function band_can_be_only_soft_deleted(): void
    {
        $this->actingAs($this->bandOwner);

        $this->json('delete', route('bands.delete', $this->band))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals(0, Band::count());
        $this->assertDatabaseHas('bands', ['id' => $this->band->id]);
        $this->assertNotNull($this->band->fresh()->deleted_at);
    }

    /** @test */
    public function when_band_is_deleted_all_its_future_rehearsals_with_attendees_and_invites_are_really_deleted(): void
    {
        $bandMembers = $this->createUsers(5);
        $this->band->members()->saveMany($bandMembers);
        $invitedUser = $this->createUser();
        $this->band->invite($invitedUser);

        $rehearsalInPast = $this->createRehearsalForBandInThePast($this->band);
        $rehearsalInPast->registerBandMembersAsAttendees();

        $rehearsalInFuture = $this->createRehearsalForBandInFuture($this->band);
        $rehearsalInFuture->registerBandMembersAsAttendees();

        $this->assertEquals(1, $this->band->invitedUsers()->count());

        $this->assertEquals(2, $this->band->rehearsals()->count());
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInPast->attendees->pluck('id')->toArray()
        );
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInFuture->attendees->pluck('id')->toArray()
        );

        $this->actingAs($this->bandOwner);

        $this->json('delete', route('bands.delete', $this->band))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertEquals(1, $this->band->rehearsals()->count());
        $this->assertDatabaseMissing('rehearsals', ['id' => $rehearsalInFuture->id]);
        $this->assertDatabaseMissing('rehearsal_user', ['rehearsal_id' => $rehearsalInFuture->id]);
        $this->assertDatabaseMissing('band_user_invites', ['band_id' => $this->band->id]);
        $this->assertDatabaseHas('rehearsal_user', ['rehearsal_id' => $rehearsalInPast->id]);
        $this->assertDatabaseHas('rehearsals', ['id' => $rehearsalInPast->id]);
        $this->assertEquals(0, $this->band->futureRehearsals()->count());
        $this->assertEquals(
            $bandMembers->pluck('id')->toArray(),
            $rehearsalInPast->fresh()->attendees->pluck('id')->toArray()
        );
        $this->assertEquals(0, $this->band->invitedUsers()->count());

    }
}

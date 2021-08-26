<?php

namespace Tests\Feature\Rehearsals\Deletion;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class RehearsalDeleteTest.
 *
 * @property $user User
 */
class RehearsalDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /** @test */
    public function user_can_delete_his_individual_rehearsal(): void
    {
        $rehearsal = $this->createRehearsalForUser($this->user);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $response = $this->json('delete', route('rehearsals.delete', $rehearsal->id));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('rehearsals', $rehearsal->toArray());
    }

    /** @test */
    public function admin_of_band_can_delete_bands_rehearsals(): void
    {
        $band = $this->createBandForUser($this->user);
        $rehearsal = $this->createRehearsalForBandInFuture($band);

        $this->assertDatabaseHas('rehearsals', ['id' => $rehearsal->id]);

        $response = $this->json('delete', route('rehearsals.delete', $rehearsal->id));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('rehearsals', ['id' => $rehearsal->id]);
    }

    /** @test */
    public function when_user_deletes_rehearsal_then_this_rehearsals_attendees_are_also_deleted(): void
    {
        $band = $this->createBandForUser($this->user);
        $rehearsalAttendees = $this->createUsers(5);
        $rehearsalAttendees->each(function (User $user) use ($band) {
            $band->addMember($user->id);
        });

        $rehearsal = $this->createRehearsalForBandInFuture($band);
        $rehearsal->registerBandMembersAsAttendees();

        $expectedAttendeesIds = $rehearsalAttendees->pluck('id')->toArray();
        $actualAttendeesIds = $rehearsal->attendees->pluck('id')->toArray();
        $this->assertEquals(
            sort($expectedAttendeesIds),
            sort($actualAttendeesIds)
        );

        $this->actingAs($this->user);
        $deletedRehearsalId = $rehearsal->id;
        $response = $this->json('delete', route('rehearsals.delete', $deletedRehearsalId));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('rehearsal_user', [
            'rehearsal_id' => $deletedRehearsalId,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->actingAs($this->user);
    }
}

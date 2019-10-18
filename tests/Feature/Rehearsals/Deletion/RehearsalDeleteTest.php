<?php

namespace Tests\Feature\Rehearsals\Deletion;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class RehearsalDeleteTest
 * @property $user User
 * @package Tests\Feature\Rehearsals\Deletion
 */
class RehearsalDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->actingAs($this->user);
    }

    /** @test */
    public function user_can_delete_his_individual_rehearsal(): void
    {
        $rehearsal = $this->createRehearsalForUser($this->user);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $response = $this->json('delete', route('rehearsals.delete', $rehearsal->id));

        $response->assertOk();

        $this->assertDatabaseMissing('rehearsals', $rehearsal->toArray());
    }

    /** @test */
    public function admin_of_band_can_delete_bands_rehearsals(): void
    {
        $band = $this->createBandForUser($this->user);
        $rehearsal = $this->createRehearsalForBandInFuture($band);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $response = $this->json('delete', route('rehearsals.delete', $rehearsal->id));

        $response->assertOk();

        $this->assertDatabaseMissing('rehearsals', $rehearsal->toArray());
    }


    /** @test */
    public function when_user_deletes_rehearsal_then_this_rehearsals_attendees_are_also_deleted(): void
    {
        $band = $this->createBandForUser($this->user);
        $rehearsalAttendees = $this->createUsers(5);
        $band->members()->saveMany($rehearsalAttendees);

        $rehearsal = $this->createRehearsalForBandInFuture($band);
        $rehearsal->registerBandMembersAsAttendees();

        $this->assertEquals(
            $rehearsalAttendees->pluck('id')->toArray(),
            $rehearsal->attendees->pluck('id')->toArray()
        );

        $this->actingAs($this->user);
        $deletedRehearsalId = $rehearsal->id;
        $response = $this->json('delete', route('rehearsals.delete', $deletedRehearsalId));

        $response->assertOk();

        $this->assertDatabaseMissing('rehearsal_user', [
            'rehearsal_id' => $deletedRehearsalId
        ]);
    }
}

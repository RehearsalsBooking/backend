<?php

namespace Tests\Feature\Bands\Members;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class BandsRegistrationTest.
 * @property User $bandAdmin
 * @property Band $band
 */
class BandMembersDeleteAuthorizationTest extends TestCase
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
    public function unauthenticated_user_cannot_delete_a_band_member(): void
    {
        $this->json('delete', route('bands.members.delete', [1, 1]))->assertUnauthorized();
    }

    /** @test */
    public function some_other_user_cannot_delete_member_of_a_band(): void
    {
        $bandMembers = $this->createUsers(2);
        $this->band->members()->saveMany($bandMembers);
        $userIdToRemoveFromBand = $this->band->members()->inRandomOrder()->first(['id'])->id;

        $this->actingAs($this->createUser());

        $this->json('delete', route('bands.members.delete', [$this->band->id, $userIdToRemoveFromBand]))
            ->assertForbidden();
    }

    /** @test */
    public function deleting_member_should_be_in_given_band(): void
    {
        $bandMembers = $this->createUsers(2);
        $this->band->members()->saveMany($bandMembers);
        $userIdToRemoveFromBand = $this->band->members()->inRandomOrder()->first(['id'])->id;

        $adminOfAnotherBand = $this->createUser();
        $anotherBand = $this->createBandForUser($adminOfAnotherBand);

        $this->actingAs($adminOfAnotherBand);

        $this->json('delete', route('bands.members.delete', [$anotherBand->id, $userIdToRemoveFromBand]))
            ->assertForbidden();
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

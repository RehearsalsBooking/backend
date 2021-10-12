<?php

namespace Tests\Feature\Bands\Members;

use App\Models\Band;
use App\Models\User;
use Tests\TestCase;

/**
 * Class BandsRegistrationTest.
 *
 * @property User $bandAdmin
 * @property Band $band
 */
class BandMembersDeleteAuthorizationTest extends TestCase
{
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
        $this->createBandMembers($this->band, 2);
        $membershipToRemoveFromBand = $this->band->memberships()->inRandomOrder()->first()->id;

        $this->actingAs($this->createUser());

        $this->json('delete', route('bands.members.delete', [$this->band->id, $membershipToRemoveFromBand]))
            ->assertForbidden();
    }

    /** @test */
    public function deleting_member_should_be_in_given_band(): void
    {
        $this->createBandMembers($this->band, 2);
        $membershipToRemoveFromBand = $this->band->memberships()->inRandomOrder()->first()->id;

        $adminOfAnotherBand = $this->createUser();
        $anotherBand = $this->createBandForUser($adminOfAnotherBand);

        $this->actingAs($adminOfAnotherBand);

        $this->json('delete', route('bands.members.delete', [$anotherBand->id, $membershipToRemoveFromBand]))
            ->assertForbidden();
    }

    /** @test */
    public function only_band_admin_can_delete_member(): void
    {
        $bandMember = $this->createUser();
        $membership = $this->createBandMembership($bandMember, $this->band);
        $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $membership->id])
        )->assertUnauthorized();
        $this->actingAs($this->createUser())->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $membership->id])
        )->assertForbidden();
    }

    /** @test */
    public function admin_of_band_cannot_leave_or_be_removed_from_his_band(): void
    {
        $membership = $this->createBandMembership($this->bandAdmin, $this->band);

        $this->assertEquals(1, $this->band->memberships()->count());
        $this->assertEquals(
            $this->bandAdmin->id,
            $this->band->members->first()->id
        );

        $this->actingAs($this->bandAdmin);

        $response = $this->json(
            'delete',
            route('bands.members.delete', [$this->band->id, $membership])
        );

        $response->assertForbidden();
    }
}

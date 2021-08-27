<?php

namespace Tests\Feature\Bands\Members;

use App\Models\Band;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class BandMembersUpdateTest extends TestCase
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
    public function band_admin_can_update_members_role(): void
    {
        $bandMember = $this->createUser();
        $oldRoles = ['old role'];
        $membership = $this->createBandMembership($bandMember, $this->band, $oldRoles);

        $this->actingAs($this->bandAdmin);

        $this->assertEquals(1, $this->band->memberships()->count());
        $this->assertEquals(
            $oldRoles,
            $this->band->fresh()->memberships->first()->roles
        );

        $newRoles = ['new role', 'new role 2'];

        $response = $this->json(
            'patch',
            route('bands.members.update', [$this->band->id, $membership->id]),
            ['roles' => $newRoles]
        );

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals(1, $this->band->memberships()->count());
        $this->assertEquals($bandMember->id, $this->band->memberships->first()->user_id);
        $this->assertEquals(
            $newRoles,
            $this->band->fresh()->memberships->first()->roles
        );
    }

    /** @test */
    public function only_band_admin_can_update_member_role(): void
    {
        $bandMember = $this->createUser();
        $membership = $this->createBandMembership($bandMember, $this->band);
        $this->json(
            'patch',
            route('bands.members.update', [$this->band->id, $membership->id]),
            ['roles' => ['role']]
        )->assertUnauthorized();
        $this->actingAs($this->createUser())->json(
            'patch',
            route('bands.members.update', [$this->band->id, $membership->id]),
            ['roles' => ['roles']]
        )->assertForbidden();
    }

    /** @test */
    public function it_requires_role_parameter(): void
    {
        $bandMember = $this->createUser();
        $membership = $this->createBandMembership($bandMember, $this->band);
        $this->actingAs($this->bandAdmin)->json(
            'patch',
            route('bands.members.update', [$this->band->id, $membership->id])
        )->assertJsonValidationErrors('roles');
    }
}

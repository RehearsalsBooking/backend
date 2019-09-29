<?php

namespace Tests\Unit\Rehearsals;

use App\Models\Band;
use App\Models\Organization;
use App\Models\Rehearsal;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function rehearsal_has_one_organization(): void
    {
        $organization = $this->createOrganization();

        $rehearsal = factory(Rehearsal::class)->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $rehearsal->organization);
    }

    /** @test */
    public function rehearsal_has_user_who_booked_this_rehearsal(): void
    {
        $user = $this->createUser();
        $rehearsal = factory(Rehearsal::class)->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $rehearsal->user);
    }

    /** @test */
    public function rehearsal_can_be_booked_by_band(): void
    {
        $user = $this->createUser();
        $band = $this->createBandForUser($user);

        /** @var Rehearsal $rehearsal */
        $rehearsal = factory(Rehearsal::class)->create([
            'user_id' => $user->id,
            'band_id' => $band->id,
        ]);

        $this->assertInstanceOf(
            Band::class,
            $rehearsal->band
        );

        $this->assertEquals(
            $band->toArray(),
            $rehearsal->band->toArray()
        );

    }
}

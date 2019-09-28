<?php

namespace Tests\Unit\Organizations;

use App\Models\Organization;
use App\Models\Rehearsal;
use App\Models\User;
use App\Models\WorkingDay;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function organization_has_one_owner(): void
    {
        /** @var User $user */
        $user = $this->createUser();

        $organization = $this->createOrganization([
            'owner_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $organization->owner);
    }

    /** @test */
    public function organization_has_rehearsals(): void
    {
        $organization = $this->createOrganization();

        factory(Rehearsal::class, 5)->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Collection::class, $organization->rehearsals);
        $this->assertEquals(5, $organization->rehearsals()->count());
        $this->assertInstanceOf(Rehearsal::class, $organization->rehearsals->first());
    }
}

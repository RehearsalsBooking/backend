<?php

namespace Tests\Unit\Organizations;

use App\Models\Price;
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

    /** @test */
    public function organization_has_prices(): void
    {
        $organization = $this->createOrganization();

        foreach (range(1, 7) as $dayOfWeek) {
            factory(Price::class)->create([
                'organization_id' => $organization->id,
                'day' => $dayOfWeek
            ]);
        }

        $this->assertInstanceOf(Collection::class, $organization->prices);
        $this->assertEquals(7, $organization->prices()->count());
        $this->assertInstanceOf(Price::class, $organization->prices->first());
    }
}

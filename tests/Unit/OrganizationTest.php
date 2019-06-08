<?php

namespace Tests\Unit;

use App\Models\Organization;
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
        $user = factory(User::class)->create();

        /** @var Organization $organization */
        $organization = factory(Organization::class)->create([
            'owner_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $organization->owner);
    }

    /** @test */
    public function an_organization_has_working_days(): void
    {
        /** @var Organization $organization */
        $organization = factory(Organization::class)->create();

        foreach (range(1, 7) as $day) {
            factory(WorkingDay::class)->create([
                'organization_id' => $organization->id,
                'day' => $day
            ]);
        }

        $this->assertInstanceOf(Collection::class, $organization->workingDays);
        $this->assertCount(7, $organization->workingDays);
        $this->assertInstanceOf(WorkingDay::class, $organization->workingDays->first());
    }
}

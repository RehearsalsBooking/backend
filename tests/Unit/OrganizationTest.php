<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\User;
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
}

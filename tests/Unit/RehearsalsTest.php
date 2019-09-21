<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\Rehearsal;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function rehearsal_has_organization(): void
    {
        $organization = factory(Organization::class)->create();

        $rehearsal = factory(Rehearsal::class)->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $rehearsal->organization);
    }

    /** @test */
    public function rehearsal_has_user(): void
    {
        $user = factory(User::class)->create();
        $rehearsal = factory(Rehearsal::class)->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $rehearsal->user);
    }
}

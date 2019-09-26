<?php

namespace Tests\Unit;

use App\Models\Band;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_may_have_organizations(): void
    {
        $user = $this->createUser();

        $this->createOrganization([
            'owner_id' => $user->id
        ]);

        $this->assertInstanceOf(Collection::class, $user->organizations);
        $this->assertInstanceOf(Organization::class, $user->organizations->first());
    }

    /** @test */
    public function user_can_be_admin_in_multiple_bands(): void
    {
        $user = $this->createUser();

        $numberOfUsersBands = 5;

        $usersBands = factory(Band::class, $numberOfUsersBands)->create([
            'admin_id' => $user->id
        ]);

        $this->assertEquals($numberOfUsersBands, $user->bands()->count());
        $this->assertEquals(
            $usersBands->toArray(),
            $user->bands->toArray()
        );
    }
}

<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\User;
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
}

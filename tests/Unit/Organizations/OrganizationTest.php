<?php

namespace Tests\Unit\Organizations;

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    /** @test */
    public function organization_has_one_owner(): void
    {
        $user = $this->createUser();

        $organization = $this->createOrganization([
            'owner_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $organization->owner);
    }

    /** @test */
    public function organization_has_rehearsals(): void
    {
        $rehearsalsCount = 5;
        $this->createRehearsalsForOrganization($this->organization, $rehearsalsCount);

        $this->assertInstanceOf(Collection::class, $this->organization->rehearsals);
        $this->assertEquals($rehearsalsCount, $this->organization->rehearsals()->count());
        $this->assertInstanceOf(Rehearsal::class, $this->organization->rehearsals->first());
    }

    /** @test */
    public function organization_has_prices(): void
    {
        foreach (range(1, 7) as $dayOfWeek) {
            factory(OrganizationPrice::class)->create([
                'organization_id' => $this->organization->id,
                'day' => $dayOfWeek,
            ]);
        }

        $this->assertInstanceOf(Collection::class, $this->organization->prices);
        $this->assertEquals(7, $this->organization->prices()->count());
        $this->assertInstanceOf(OrganizationPrice::class, $this->organization->prices->first());
    }

    /** @test */
    public function organization_has_banned_users(): void
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach (range(1, 5) as $_) {
            $user = $this->createUser();
            OrganizationUserBan::create([
                'organization_id' => $this->organization->id,
                'user_id' => $user->id,
                'comment' => 'some reason to ban user',
            ]);
        }

        $this->assertInstanceOf(Collection::class, $this->organization->bannedUsers);
        $this->assertEquals(5, $this->organization->bannedUsers()->count());
        $this->assertInstanceOf(User::class, $this->organization->bannedUsers->first());
    }

    /** @test */
    public function organization_has_users_who_favorited_it(): void
    {
        $favoritedUsers = $this->createUsers(3);

        $this->organization->favoritedUsers()->sync($favoritedUsers->pluck('id')->toArray());

        $this->assertEquals($this->organization->favoritedUsers()->count(), $favoritedUsers->count());
        $this->assertInstanceOf(User::class, $this->organization->favoritedUsers->first());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = $this->createOrganization();
    }
}

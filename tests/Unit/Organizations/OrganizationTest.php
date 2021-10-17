<?php

namespace Tests\Unit\Organizations;

use App\Models\City;
use App\Models\Organization\OrganizationPrice;
use App\Models\Organization\OrganizationRoom;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
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
        $organization = $this->createOrganization();
        $this->createRehearsalsForOrganization($organization, $rehearsalsCount);

        $this->assertInstanceOf(Collection::class, $organization->rehearsals);
        $this->assertEquals($rehearsalsCount, $organization->rehearsals()->count());
        $this->assertInstanceOf(Rehearsal::class, $organization->rehearsals->first());
    }

    /** @test */
    public function organization_has_prices(): void
    {
        $organization = $this->createOrganization();
        foreach (range(0, 6) as $dayOfWeek) {
            OrganizationPrice::factory()->create([
                'organization_id' => $organization->id,
                'day' => $dayOfWeek,
            ]);
        }

        $this->assertInstanceOf(Collection::class, $organization->prices);
        $this->assertEquals(7, $organization->prices()->count());
        $this->assertInstanceOf(OrganizationPrice::class, $organization->prices->first());
    }

    /** @test */
    public function organization_has_banned_users(): void
    {
        $organization = $this->createOrganization();

        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach (range(1, 5) as $_) {
            $user = $this->createUser();
            OrganizationUserBan::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'comment' => 'some reason to ban user',
            ]);
        }

        $this->assertInstanceOf(Collection::class, $organization->bannedUsers);
        $this->assertEquals(5, $organization->bannedUsers()->count());
        $this->assertInstanceOf(User::class, $organization->bannedUsers->first());
    }

    /** @test */
    public function organization_has_users_who_favorited_it(): void
    {
        $favoritedUsers = $this->createUsers(3);

        $organization = $this->createOrganization();
        $organization->favoritedUsers()->sync($favoritedUsers->pluck('id')->toArray());

        $this->assertEquals($organization->favoritedUsers()->count(), $favoritedUsers->count());
        $this->assertInstanceOf(User::class, $organization->favoritedUsers->first());
    }

    /** @test */
    public function organization_belongs_to_city(): void
    {
        $city = $this->createCity();
        $organization = $this->createOrganization(['city_id' => $city->id]);

        $this->assertInstanceOf(City::class, $organization->city);
        $this->assertEquals($city->id, $organization->city->id);
    }

    /** @test */
    public function organization_has_multiple_rooms(): void
    {
        $organization = $this->createOrganization();

        $lightRoom = $this->createOrganizationRoom($organization);
        $darkRoom = $this->createOrganizationRoom($organization);

        $organizationRooms = $organization->rooms;

        $this->assertEquals(2, $organizationRooms->count());

        $this->assertInstanceOf(OrganizationRoom::class, $organizationRooms[0]);
        $this->assertEquals($lightRoom->id, $organizationRooms[0]->id);

        $this->assertInstanceOf(OrganizationRoom::class, $organizationRooms[1]);
        $this->assertEquals($darkRoom->id, $organizationRooms[1]->id);
    }
}

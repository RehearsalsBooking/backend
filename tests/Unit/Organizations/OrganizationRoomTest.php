<?php

namespace Tests\Unit\Organizations;

use App\Models\City;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoomPrice;
use App\Models\Organization\OrganizationRoom;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrganizationRoomTest extends TestCase
{
    /** @test */
    public function room_belongs_to_organization(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->assertInstanceOf(Organization::class, $room->organization);
        $this->assertEquals($organization->id, $room->organization->id);
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

    /** @test */
    public function room_has_prices(): void
    {
        $room = $this->createOrganizationRoom($this->createOrganization());
        foreach (range(0, 6) as $dayOfWeek) {
            OrganizationRoomPrice::factory()->create([
                'organization_room_id' => $room->id,
                'day' => $dayOfWeek,
            ]);
        }

        $this->assertInstanceOf(Collection::class, $room->prices);
        $this->assertEquals(7, $room->prices()->count());
        $this->assertInstanceOf(OrganizationRoomPrice::class, $room->prices->first());

        $price = $room->prices()->first();
        $this->assertInstanceOf(OrganizationRoom::class, $price->room);
        $this->assertEquals($room->id, $price->room->id);
    }
}

<?php

namespace Database\Factories;

use App\Models\Organization\OrganizationRoom;
use App\Models\Organization\OrganizationRoomPrice;
use Belamov\PostgresRange\Ranges\TimeRange;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationPriceFactory extends Factory
{
    protected $model = OrganizationRoomPrice::class;

    public function definition(): array
    {
        return [
            'day' => $this->faker->numberBetween(0, 6),
            'price' => $this->faker->randomNumber(3),
            'organization_room_id' => OrganizationRoom::factory(),
            'time' => new TimeRange('00:00', '23:59'),
        ];
    }
}

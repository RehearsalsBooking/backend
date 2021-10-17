<?php

namespace Database\Factories;

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationRoomFactory extends Factory
{
    protected $model = OrganizationRoom::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'organization_id' => Organization::factory()
        ];
    }
}

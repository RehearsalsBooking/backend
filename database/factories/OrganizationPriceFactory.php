<?php

namespace Database\Factories;

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use Belamov\PostgresRange\Ranges\TimeRange;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationPriceFactory extends Factory
{
    protected $model = OrganizationPrice::class;

    public function definition(): array
    {
        return [
            'day' => $this->faker->numberBetween(0, 6),
            'price' => $this->faker->randomNumber(3),
            'organization_id' => Organization::factory(),
            'time' => new TimeRange('00:00', '23:59'),
        ];
    }
}

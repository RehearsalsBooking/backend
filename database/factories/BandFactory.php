<?php

namespace Database\Factories;

use App\Models\Band;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BandFactory extends Factory
{
    protected $model = Band::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'admin_id' => User::factory(),
            'bio' => $this->faker->text,
        ];
    }
}

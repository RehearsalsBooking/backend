<?php

namespace Database\Factories;

use App\Models\BandGenre;
use Illuminate\Database\Eloquent\Factories\Factory;

class BandGenreFactory extends Factory
{
    protected $model = BandGenre::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
        ];
    }
}

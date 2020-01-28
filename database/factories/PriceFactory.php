<?php

/** @var Factory $factory */

use App\Models\Organization;
use App\Models\Price;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Price::class, static function (Faker $faker) {
    return [
        'day' => $faker->numberBetween(1, 7),
        'price' => $faker->randomNumber(3),
        'organization_id' => fn () => factory(Organization::class)->create()->id,
        'starts_at' => '10:00',
        'ends_at' => '23:59'
    ];
});

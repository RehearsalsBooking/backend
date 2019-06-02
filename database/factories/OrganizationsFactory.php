<?php

/* @var $factory Factory */

use App\Models\Organization;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Organization::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
        'address' => $faker->address,
        'verified' => true,
    ];
});

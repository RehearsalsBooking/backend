<?php

/* @var $factory Factory */

use App\Models\Organization;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Organization::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
        'address' => $faker->address,
        'description' => $faker->paragraph,
        'verified' => true,
        'owner_id' => static function () {
            return factory(User::class)->create()->id;
        }
    ];
});

<?php

/** @var Factory $factory */

use App\Model;
use App\Models\Band;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Band::class, static function (Faker $faker) {
    return [
        'name' => implode(' ', $faker->words(2)),
        'admin_id' => static function () {
            return factory(User::class)->create()->id;
        }
    ];
});

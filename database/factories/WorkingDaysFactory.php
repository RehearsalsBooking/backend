<?php

/* @var $factory Factory */

use App\Model;
use App\Models\Organization;
use App\Models\WorkingDay;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(WorkingDay::class, static function (Faker $faker) {

    $opensAt = $faker->time('H:i', '23:00');
    $closesAt = $faker->time('H:i', '23:00');

    if ($opensAt > $closesAt) {
        $temp = $opensAt;
        $opensAt = $closesAt;
        $closesAt = $temp;
    }

    return [
        'day' => $faker->randomElement([1, 2, 3, 4, 5, 6, 7]),
        'opens_at' => $opensAt,
        'closes_at' => $closesAt,
        'organization_id' => static function () {
            return factory(Organization::class)->create()->id;
        }
    ];
});

<?php

/** @var Factory $factory */

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use Belamov\PostgresRange\Ranges\TimeRange;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(OrganizationPrice::class, static function (Faker $faker) {
    return [
        'day' => $faker->numberBetween(0, 6),
        'price' => $faker->randomNumber(3),
        'organization_id' => fn () => factory(Organization::class)->create()->id,
        'time' => new TimeRange('00:00', '23:59'),
    ];
});

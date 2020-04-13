<?php

/** @var Factory $factory */

use App\Models\Organization\Organization;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Rehearsal::class, static function (Faker $faker) {
    $startsAt = Carbon::create(
        2019,
        $faker->numberBetween(1, 12),
        $faker->numberBetween(1, 20),
        $faker->numberBetween(8, 20),
        0,
        0
    );

    $endsAt = $startsAt->copy()->addHours(2);

    return [
        'organization_id' => static function () {
            return factory(Organization::class)->create()->id;
        },

        'user_id' => static function () {
            return factory(User::class)->create()->id;
        },

        'is_confirmed' => true,
        'time' => new TimestampRange(
            $startsAt->toDateTimeString(),
            $endsAt->toDateTimeString(),
        ),
        'price' => $faker->randomNumber(3),
    ];
});

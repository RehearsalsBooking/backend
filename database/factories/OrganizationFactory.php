<?php

/* @var $factory Factory */

use App\Models\Organization\Organization;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

// restrict random coordinates of organizations to be
// in 5km radius of (57.144179, 65.553132) (somewhere in my city)
// https://stackoverflow.com/questions/3067530/how-can-i-get-minimum-and-maximum-latitude-and-longitude-using-current-location
$latitude = 57.144179;
$longitude = 65.553132;
$radiusInKM = 5.00;
$minLat = $latitude - ($radiusInKM / 111.12);
$maxLat = $latitude + ($radiusInKM / 111.12);
$minLon = $longitude - ($radiusInKM) / abs(cos($longitude / 180.0 * M_PI) * 111.12);
$maxLon = $longitude + ($radiusInKM) / abs(cos($longitude / 180.0 * M_PI) * 111.12);

$factory->define(Organization::class, static function (Faker $faker) use ($maxLon, $minLon, $maxLat, $minLat) {
    //57.144179, 65.553132
    return [
        'name' => $faker->word,
        'address' => $faker->address,
        'gear' => $faker->paragraph,
        'coordinates' => "({$faker->latitude($minLat, $maxLat)},{$faker->longitude($minLon, $maxLon)})",
        'is_active' => true,
        'avatar' => 'https://picsum.photos/300/200',
        'owner_id' => static function () {
            return factory(User::class)->create()->id;
        },
    ];
});

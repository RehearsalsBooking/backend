<?php

/* @var $factory Factory */

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationEquipment;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(OrganizationEquipment::class, static function (Faker $faker) {
    return [
        'item_description' => $faker->word,
        'model' => implode(' ', $faker->words(2)),
        'photo' => 'https://picsum.photos/300/200',
        'organization_id' => static fn () => factory(Organization::class)->create()->id,
    ];
});

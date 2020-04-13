<?php

/** @var Factory $factory */

use App\Models\Band;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Invite::class, static function () {
    return [
        'user_id' => static function () {
            return factory(User::class)->create()->id;
        },
        'band_id' => static function () {
            return factory(Band::class)->create()->id;
        },
    ];
});

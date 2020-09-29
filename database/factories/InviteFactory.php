<?php

namespace Database\Factories;

use App\Models\Band;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InviteFactory extends Factory
{
    protected $model = Invite::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'band_id' => Band::factory(),
        ];
    }
}
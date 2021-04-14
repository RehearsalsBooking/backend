<?php

namespace Database\Factories;

use App\Models\Band;
use App\Models\Invite;
use Illuminate\Database\Eloquent\Factories\Factory;

class InviteFactory extends Factory
{
    protected $model = Invite::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->safeEmail,
            'band_id' => Band::factory(),
            'status' => Invite::STATUS_SENT,
        ];
    }
}

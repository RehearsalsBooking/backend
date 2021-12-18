<?php

namespace Database\Factories;

use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RehearsalFactory extends Factory
{
    protected $model = Rehearsal::class;

    public function definition(): array
    {
        return [
            'organization_room_id' => OrganizationRoom::factory(),
            'user_id' => User::factory(),
            'is_paid' => true,
            'time' => $this->getRehearsalTime(),
            'price' => $this->faker->numerify('%00'),
        ];
    }

    /**
     * @return TimestampRange
     */
    private function getRehearsalTime(): TimestampRange
    {
        $startsAt = Carbon::parse($this->faker->dateTimeBetween('-1 year', '+1 month'))->setSecond(0)->setMinute(0);
        $endsAt = $startsAt->copy()->addHours(2);

        return new TimestampRange($startsAt, $endsAt,);
    }
}

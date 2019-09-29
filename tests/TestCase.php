<?php

namespace Tests;

use App\Models\Band;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithFaker;

    /**
     * @param array $attributes
     * @return Organization
     */
    protected function createOrganization(array $attributes = []): Organization
    {
        return factory(Organization::class)->create($attributes);
    }

    /**
     * @return User
     */
    protected function createUser(): User
    {
        return factory(User::class)->create();
    }

    /**
     * @param int $count
     * @return mixed
     */
    protected function createUsers(int $count)
    {
        return factory(User::class, $count)->create();
    }

    /**
     * @return Carbon
     */
    protected function generateRandomDate(): Carbon
    {
        return Carbon::create(
            2019,
            $this->faker->numberBetween(1, 12),
            $this->faker->numberBetween(1, 20),
            $this->faker->numberBetween(8, 20)
        );
    }

    /**
     * @param $hour
     * @param $minute
     * @return string
     */
    protected function getDateTimeAt($hour, $minute): string
    {
        return Carbon::now()->addDay()->setHour($hour)->setMinute($minute)->setSeconds(0)->toDateTimeString();
    }

    /**
     * @param User $user
     * @return Band
     */
    protected function createBandForUser(User $user): Band
    {
        return factory(Band::class)->create([
            'admin_id' => $user->id
        ]);
    }
}

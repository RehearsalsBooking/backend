<?php

namespace Tests;

use App\Models\Band;
use App\Models\Invite;
use App\Models\Organization;
use App\Models\Price;
use App\Models\Rehearsal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\Feature\Rehearsals\RehearsalRescheduleValidationTest;

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
     * @return Collection
     */
    protected function createUsers(int $count): Collection
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
     * @param User $user
     * @return Band
     */
    protected function createBandForUser(User $user): Band
    {
        return factory(Band::class)->create([
            'admin_id' => $user->id
        ]);
    }

    /**
     * @return Band
     */
    protected function createBand(): Band
    {
        return factory(Band::class)->create();
    }

    /**
     * @param Organization $organization
     * @param $startsAt
     * @param $endsAt
     * @param Band|null $band
     * @param bool $isConfirmed
     * @param User|null $user
     * @return mixed
     */
    protected function createRehearsal(
        ?Organization $organization,
        $startsAt,
        $endsAt,
        Band $band = null,
        bool $isConfirmed = false,
        User $user = null
    ): Rehearsal {
        $user = $user ?? factory(User::class)->create();

        return factory(Rehearsal::class)->create([
            'starts_at' => $this->getDateTimeAt($startsAt, 00),
            'ends_at' => $this->getDateTimeAt($endsAt, 00),
            'organization_id' => optional($organization)->id,
            'band_id' => optional($band)->id,
            'is_confirmed' => $isConfirmed,
            'user_id' => $user->id,
        ]);
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
     * @return array
     */
    protected function getRehearsalTime(): array
    {
        /**
         * @var $rehearsalStart Carbon
         */
        $rehearsalStart = Carbon::now()->addHour()->setMinutes(30)->setSeconds(0);

        /**
         * @var $rehearsalEnd Carbon
         */
        $rehearsalEnd = $rehearsalStart->copy()->addHours(2);

        return [
            'starts_at' => $rehearsalStart->toDateTimeString(),
            'ends_at' => $rehearsalEnd->toDateTimeString()
        ];
    }

    /**
     * @param array $parameters
     * @return Invite
     */
    protected function createInvite(array $parameters = []): Invite
    {
        return factory(Invite::class)->create($parameters);
    }

    /**
     * @param User $user
     * @return Rehearsal
     */
    protected function createRehearsalForUser(User $user): Rehearsal
    {
        return factory(Rehearsal::class)->create([
            'user_id' => $user->id,
            'starts_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addDay()->addHours(2),
        ]);
    }

    /**
     * @param Band $band
     * @return Rehearsal
     */
    protected function createRehearsalForBandInFuture(Band $band): Rehearsal
    {
        return factory(Rehearsal::class)->create([
            'band_id' => $band->id,
            'starts_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addDay()->addHours(2),
        ]);
    }

    /**
     * @param Band $band
     * @return Rehearsal
     */
    protected function createRehearsalForBandInThePast(Band $band): Rehearsal
    {
        return factory(Rehearsal::class)->create([
            'band_id' => $band->id,
            'starts_at' => Carbon::now()->subDays(3),
            'ends_at' => Carbon::now()->subDays(3)->addHours(2),
        ]);
    }

    /**
     * @param Organization $organization
     * @param string $startsAt
     * @param string $endsAt
     */
    protected function createPricesForOrganization(Organization $organization, string $startsAt = '00:00', string $endsAt = '24:00'): void
    {
        foreach (range(1, 7) as $dayOfWeek) {
            factory(Price::class)->create([
                'organization_id' => $organization->id,
                'day' => $dayOfWeek,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
        }
    }
}

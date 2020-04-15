<?php

namespace Tests;

use App\Models\Band;
use App\Models\Invite;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;

    protected function banUsers(Organization $organization, int $usersCount = 3): Collection
    {
        $bannedUsers = $this->createUsers($usersCount);
        $bannedUsers->each(
            static function (User $user) use ($organization) {
                OrganizationUserBan::create(
                    [
                        'user_id' => $user->id,
                        'organization_id' => $organization->id,
                        'comment' => 'reason to ban',
                    ]
                );
            }
        );

        return $bannedUsers;
    }

    /**
     * @param  int  $count
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
     * @param  User  $user
     * @return Band
     */
    protected function createBandForUser(User $user): Band
    {
        return factory(Band::class)->create(
            [
                'admin_id' => $user->id,
            ]
        );
    }

    /**
     * @return Band
     */
    protected function createBand(): Band
    {
        return factory(Band::class)->create();
    }

    /**
     * @param $startsAt
     * @param $endsAt
     * @param  Organization  $organization
     * @param  Band|null  $band
     * @param  bool  $isConfirmed
     * @param  User|null  $user
     * @return mixed
     */
    protected function createRehearsal(
        $startsAt,
        $endsAt,
        Organization $organization = null,
        Band $band = null,
        bool $isConfirmed = false,
        User $user = null
    ): Rehearsal {
        $user ??= $this->createUser();
        $organization ??= $this->createOrganization();

        return factory(Rehearsal::class)->create(
            [
                'time' => new TimestampRange(
                    $this->getDateTimeAt($startsAt, 00),
                    $this->getDateTimeAt($endsAt, 00),
                    '[',
                    ')'
                ),
                'organization_id' => $organization->id,
                'band_id' => optional($band)->id,
                'is_confirmed' => $isConfirmed,
                'user_id' => $user->id,
            ]
        );
    }

    /**
     * @return User
     */
    protected function createUser(): User
    {
        return factory(User::class)->create();
    }

    /**
     * @param  array  $attributes
     * @return Organization
     */
    protected function createOrganization(array $attributes = []): Organization
    {
        return factory(Organization::class)->create($attributes);
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
     * @param  User  $user
     * @param  array  $params
     * @return Organization
     */
    protected function createOrganizationForUser(User $user, array $params = []): Organization
    {
        $organization = factory(Organization::class)->create(array_merge($params, ['owner_id' => $user->id]));
        $this->createPricesForOrganization($organization);

        return $organization;
    }

    /**
     * @param  Organization  $organization
     * @param  string  $startsAt
     * @param  string  $endsAt
     */
    protected function createPricesForOrganization(
        Organization $organization,
        string $startsAt = '00:00',
        string $endsAt = '24:00'
    ): void {
        foreach (range(1, 7) as $dayOfWeek) {
            factory(OrganizationPrice::class)->create(
                [
                    'organization_id' => $organization->id,
                    'day' => $dayOfWeek,
                    'time' => new TimeRange($startsAt, $endsAt),
                ]
            );
        }
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
            'ends_at' => $rehearsalEnd->toDateTimeString(),
        ];
    }

    /**
     * @param  array  $parameters
     * @return Invite
     */
    protected function createInvite(array $parameters = []): Invite
    {
        return factory(Invite::class)->create($parameters);
    }

    /**
     * @param  User  $user
     * @return Rehearsal
     */
    protected function createRehearsalForUser(User $user): Rehearsal
    {
        return factory(Rehearsal::class)->create(
            [
                'user_id' => $user->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay()->toDateTimeString(),
                    Carbon::now()->addDay()->addHours(2)->toDateTimeString(),
                ),
            ]
        );
    }

    /**
     * @param $start
     * @param $end
     * @return TimestampRange
     */
    protected function getTimestampRange($start, $end): TimestampRange
    {
        return new TimestampRange(
            $start,
            $end
        );
    }

    /**
     * @param  Organization  $organization
     * @param  int  $amount
     * @return Rehearsal|Collection
     */
    protected function createRehearsalsForOrganization(Organization $organization, int $amount = 1)
    {
        $rehearsals = [];
        foreach (range(1, $amount) as $index) {
            $rehearsals[] = factory(Rehearsal::class)->create(
                [
                    'organization_id' => $organization->id,
                    'time' => $this->getTimestampRange(
                        Carbon::now()->addDays($index)->toDateTimeString(),
                        Carbon::now()->addDays($index)->addHours(2)->toDateTimeString(),
                    ),
                ]
            );
        }

        return collect($rehearsals);
    }

    /**
     * @param  Band  $band
     * @return Rehearsal
     */
    protected function createRehearsalForBandInFuture(Band $band): Rehearsal
    {
        return factory(Rehearsal::class)->create(
            [
                'band_id' => $band->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay()->toDateTimeString(),
                    Carbon::now()->addDay()->addHours(2)->toDateTimeString(),
                ),
            ]
        );
    }

    /**
     * @param  User  $user
     * @param  Organization  $organization
     * @return Rehearsal
     */
    protected function createRehearsalForUserInFuture(User $user, Organization $organization): Rehearsal
    {
        $rehearsal = factory(Rehearsal::class)->create(
            [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay()->toDateTimeString(),
                    Carbon::now()->addDay()->addHours(2)->toDateTimeString(),
                ),
            ]
        );

        /** @var Rehearsal $rehearsal */
        $rehearsal->attendees()->attach($user->id);

        return $rehearsal;
    }

    /**
     * @param  User  $user
     * @param  Organization  $organization
     * @return Rehearsal
     */
    protected function createRehearsalForUserInPast(User $user, Organization $organization): Rehearsal
    {
        $rehearsal = factory(Rehearsal::class)->create(
            [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->subDays(3)->toDateTimeString(),
                    Carbon::now()->subDays(3)->addHours(2)->toDateTimeString(),
                ),
            ]
        );

        /** @var Rehearsal $rehearsal */
        $rehearsal->attendees()->attach($user->id);

        return $rehearsal;
    }

    /**
     * @param  Band  $band
     * @return Rehearsal
     */
    protected function createRehearsalForBandInThePast(Band $band): Rehearsal
    {
        return factory(Rehearsal::class)->create(
            [
                'band_id' => $band->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->subDays(3)->toDateTimeString(),
                    Carbon::now()->subDays(3)->addHours(2)->toDateTimeString()
                ),
            ]
        );
    }
}

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
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Collection;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(
            ThrottleRequests::class
        );
    }

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
        return User::factory()->count($count)->create();
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
        return Band::factory()->create(
            [
                'admin_id' => $user->id,
            ]
        );
    }

    /**
     * @param  array  $attributes
     * @return Band
     */
    protected function createBand(array $attributes = []): Band
    {
        return Band::factory()->create($attributes);
    }

    /**
     * @param $startsAt
     * @param $endsAt
     * @param  Organization|null  $organization
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

        return Rehearsal::factory()->create(
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
     * @param  array  $attributes
     * @return User
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * @param  array  $attributes
     * @return Organization
     */
    protected function createOrganization(array $attributes = []): Organization
    {
        return Organization::factory()->create($attributes);
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
     * @param  array  $attributes
     * @param  int  $count
     * @return Organization[]|Collection
     */
    protected function createOrganizations(int $count = 1, array $attributes = []): Collection
    {
        return Organization::factory()->count($count)->create($attributes);
    }

    /**
     * @param  User  $user
     * @param  array  $params
     * @return Organization
     */
    protected function createOrganizationForUser(User $user, array $params = []): Organization
    {
        $organization = Organization::factory()->create(array_merge($params, ['owner_id' => $user->id]));
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
        string $endsAt = '23:59'
    ): void {
        foreach (range(0, 6) as $dayOfWeek) {
            OrganizationPrice::factory()->create(
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
        $rehearsalStart = Carbon::now()->addHour()->setMinutes(30)->setSeconds(0);

        $rehearsalEnd = $rehearsalStart->copy()->addHours(2);

        return [
            'starts_at' => $rehearsalStart,
            'ends_at' => $rehearsalEnd,
        ];
    }

    /**
     * @param  array  $parameters
     * @return Invite
     */
    protected function createInvite(array $parameters = []): Invite
    {
        return Invite::factory()->create($parameters);
    }

    /**
     * @param  User  $user
     * @return Rehearsal
     */
    protected function createRehearsalForUser(User $user): Rehearsal
    {
        return Rehearsal::factory()->create(
            [
                'user_id' => $user->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay(),
                    Carbon::now()->addDay()->addHours(2),
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
            $rehearsals[] = Rehearsal::factory()->create(
                [
                    'organization_id' => $organization->id,
                    'time' => $this->getTimestampRange(
                        Carbon::now()->addDays($index),
                        Carbon::now()->addDays($index)->addHours(2),
                    ),
                ]
            );
        }

        return collect($rehearsals);
    }

    /**
     * @param  Band  $band
     * @param  User|null  $user
     * @return Rehearsal
     */
    protected function createRehearsalForBandInFuture(Band $band, ?User $user = null): Rehearsal
    {
        $user ??= $this->createUser();

        return Rehearsal::factory()->create(
            [
                'user_id' => $user->id,
                'band_id' => $band->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay(),
                    Carbon::now()->addDay()->addHours(2),
                ),
            ]
        );
    }

    /**
     * @param  User|null  $user
     * @param  Organization|null  $organization
     * @return Rehearsal
     */
    protected function createRehearsalForUserInFuture(?User $user = null, ?Organization $organization = null): Rehearsal
    {
        $user ??= $this->createUser();
        $organization ??= $this->createOrganization();

        return Rehearsal::factory()->create(
            [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay(),
                    Carbon::now()->addDay()->addHours(2),
                ),
            ]
        );
    }

    /**
     * @param  User  $user
     * @param  Organization  $organization
     * @return Rehearsal
     */
    protected function createRehearsalForUserInPast(User $user, Organization $organization): Rehearsal
    {
        return Rehearsal::factory()->create(
            [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->subDays(3),
                    Carbon::now()->subDays(3)->addHours(2),
                ),
            ]
        );
    }

    /**
     * @param  Band  $band
     * @return Rehearsal
     */
    protected function createRehearsalForBandInThePast(Band $band): Rehearsal
    {
        return Rehearsal::factory()->create(
            [
                'band_id' => $band->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->subDays(3),
                    Carbon::now()->subDays(3)->addHours(2)
                ),
            ]
        );
    }
}

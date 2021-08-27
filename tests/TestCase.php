<?php

namespace Tests;

use App\Models\Band;
use App\Models\BandMembership;
use App\Models\Genre;
use App\Models\Invite;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
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

    protected function createBandForUser(User $user): Model|EloquentCollection|Band
    {
        return Band::factory()->create(
            [
                'admin_id' => $user->id,
            ]
        );
    }

    protected function createBand(array $attributes = []): Model|EloquentCollection|Band
    {
        return Band::factory()->create($attributes);
    }

    protected function createRehearsal(
        $startsAt,
        $endsAt,
        Organization $organization = null,
        Band $band = null,
        bool $isConfirmed = false,
        User $user = null
    ): EloquentCollection|Model|Rehearsal {
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
                'is_paid' => $isConfirmed,
                'user_id' => $user->id,
            ]
        );
    }

    protected function createUser(array $attributes = []): EloquentCollection|Model|User
    {
        return User::factory()->create($attributes);
    }

    protected function createOrganization(array $attributes = []): EloquentCollection|Model|Organization
    {
        return Organization::factory()->create($attributes);
    }

    protected function getDateTimeAt($hour, $minute): string
    {
        return Carbon::now()->addDay()->setHour($hour)->setMinute($minute)->setSeconds(0)->toDateTimeString();
    }

    protected function createOrganizations(int $count = 1, array $attributes = []): Collection
    {
        return Organization::factory()->count($count)->create($attributes);
    }

    protected function createOrganizationForUser(User $user, array $params = []): EloquentCollection|Model|Organization
    {
        /** @var Organization $organization */
        $organization = Organization::factory()->create(array_merge($params, ['owner_id' => $user->id]));
        $this->createPricesForOrganization($organization);

        return $organization;
    }

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

    protected function getRehearsalTime(): array
    {
        $rehearsalStart = Carbon::now()->addHour()->setMinutes(30)->setSeconds(0);

        $rehearsalEnd = $rehearsalStart->copy()->addHours(2);

        return [
            'starts_at' => $rehearsalStart,
            'ends_at' => $rehearsalEnd,
        ];
    }

    protected function createInvite(array $attributes = []): EloquentCollection|Model|Invite
    {
        return Invite::factory()->create($attributes);
    }

    protected function createRehearsalForUser(User $user): EloquentCollection|Model|Rehearsal
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

    protected function getTimestampRange($start, $end): TimestampRange
    {
        return new TimestampRange(
            $start,
            $end
        );
    }

    protected function createRehearsalsForOrganization(
        Organization $organization,
        int $amount = 1
    ): Rehearsal|Collection {
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

    protected function createRehearsalForBandInFuture(
        Band $band,
        ?User $user = null
    ): EloquentCollection|Model|Rehearsal {
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

    protected function createRehearsalForUserInFuture(
        ?User $user = null,
        ?Organization $organization = null
    ): EloquentCollection|Model|Rehearsal {
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

    protected function createRehearsalForUserInPast(
        User $user,
        Organization $organization = null
    ): EloquentCollection|Model|Rehearsal {
        $organization ??= $this->createOrganization();

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

    protected function createRehearsalForBandInThePast(Band $band): EloquentCollection|Model|Rehearsal
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

    protected function createGenre(): EloquentCollection|Model|Genre
    {
        return Genre::factory()->create();
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createBandMembership(User $user, Band $band, array $roles = []): BandMembership
    {
        return BandMembership::factory()->create([
            'band_id' => $band->id,
            'user_id' => $user->id,
            'roles' => $roles,
        ]);
    }

    protected function createBandMembers(Band $band, int $count = 1): Collection
    {
        $members = $this->createUsers($count);
        $members->each(function (User $user) use ($band) {
            $this->createBandMembership($user, $band);
        });

        return $members;
    }
}

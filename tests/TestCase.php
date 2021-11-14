<?php

namespace Tests;

use App\Models\Band;
use App\Models\BandMembership;
use App\Models\City;
use App\Models\Genre;
use App\Models\Invite;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoomPrice;
use App\Models\Organization\OrganizationRoom;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Collection;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;
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

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createBandForUser(User $user): Band
    {
        return Band::factory()->create(
            [
                'admin_id' => $user->id,
            ]
        );
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createBand(array $attributes = []): Band
    {
        return Band::factory()->create($attributes);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createRehearsal(
        int $startsAt = 10,
        int $endsAt = 12,
        OrganizationRoom $room = null,
        Band $band = null,
        bool $isPaid = false,
        User $user = null
    ): Rehearsal {
        $user ??= $this->createUser();
        $room ??= $this->createOrganizationRoom();

        return Rehearsal::factory()->create(
            [
                'time' => new TimestampRange(
                    $this->getDateTimeAt($startsAt, 00),
                    $this->getDateTimeAt($endsAt, 00),
                    '[',
                    ')'
                ),
                'organization_room_id' => $room->id,
                'band_id' => optional($band)->id,
                'is_paid' => $isPaid,
                'user_id' => $user->id,
            ]
        );
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createOrganization(array $attributes = []): Organization
    {
        return Organization::factory()->create($attributes);
    }

    protected function getDateTimeAt($hour, $minute): string
    {
        return Carbon::now()->addDay()->setHour($hour)->setMinute($minute)->setSeconds(0)->toDateTimeString();
    }

    protected function createOrganizations(int $count = 1, array $attributes = []): Collection
    {
        return Organization::factory()
            ->count($count)
            ->create($attributes)
            ->each(
                fn(Organization $organization) => OrganizationRoom::factory()->create([
                    'organization_id' => $organization->id
                ])
            );
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createOrganizationForUser(User $user, array $params = []): Organization
    {
        return Organization::factory()->create(array_merge($params, ['owner_id' => $user->id]));
    }

    protected function createPricesForOrganization(
        Organization $organization,
        string $startsAt = '00:00',
        string $endsAt = '23:59'
    ): void {
        $organization->rooms->each(
            function (OrganizationRoom $room) use ($startsAt, $endsAt) {
                foreach (range(0, 6) as $dayOfWeek) {
                    OrganizationRoomPrice::factory()
                        ->create(
                            [
                                'organization_room_id' => $room->id,
                                'day' => $dayOfWeek,
                                'time' => new TimeRange($startsAt, $endsAt),
                            ]
                        );
                }
            }
        );
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

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createInvite(array $attributes = []): Invite
    {
        return Invite::factory()->create($attributes);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
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

    protected function getTimestampRange($start, $end): TimestampRange
    {
        return new TimestampRange(
            $start,
            $end
        );
    }

    protected function createRehearsalsForRoom(OrganizationRoom $room, int $amount = 1): Collection
    {
        $rehearsals = [];
        foreach (range(1, $amount) as $index) {
            $rehearsals[] = Rehearsal::factory()->create(
                [
                    'organization_room_id' => $room->id,
                    'time' => $this->getTimestampRange(
                        Carbon::now()->addDays($index),
                        Carbon::now()->addDays($index)->addHours(2),
                    ),
                ]
            );
        }

        return collect($rehearsals);
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createRehearsalForBandInFuture(Band $band = null): Rehearsal
    {
        $band ??= $this->createBand();

        return Rehearsal::factory()->create(
            [
                'user_id' => $band->admin_id,
                'band_id' => $band->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay(),
                    Carbon::now()->addDay()->addHours(2),
                ),
            ]
        );
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createRehearsalForUserInFuture(?User $user = null, ?OrganizationRoom $room = null): Rehearsal
    {
        $user ??= $this->createUser();
        $room ??= $this->createOrganizationRoom();

        return Rehearsal::factory()->create(
            [
                'user_id' => $user->id,
                'organization_room_id' => $room->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->addDay(),
                    Carbon::now()->addDay()->addHours(2),
                ),
            ]
        );
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createRehearsalForUserInPast(User $user, OrganizationRoom $room = null): Rehearsal
    {
        $room ??= $this->createOrganizationRoom();

        return Rehearsal::factory()->create(
            [
                'user_id' => $user->id,
                'organization_room_id' => $room->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->subDays(3),
                    Carbon::now()->subDays(3)->addHours(2),
                ),
            ]
        );
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createRehearsalForBandInThePast(Band $band): Rehearsal
    {
        return Rehearsal::factory()->create(
            [
                'user_id' => $band->admin_id,
                'band_id' => $band->id,
                'time' => $this->getTimestampRange(
                    Carbon::now()->subDays(3),
                    Carbon::now()->subDays(3)->addHours(2)
                ),
            ]
        );
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createGenre(): Genre
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

    protected function addBandMembers(Band $band, int $count = 1): Collection
    {
        $members = $this->createUsers($count);
        $members->each(function (User $user) use ($band) {
            $this->createBandMembership($user, $band);
        });

        return $members;
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createCity(): City
    {
        return City::factory()->create();
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    protected function createOrganizationRoom(?Organization $organization = null): OrganizationRoom
    {
        $organization = $organization ?? $this->createOrganization();
        return OrganizationRoom::factory()->create([
            'organization_id' => $organization->id,
        ]);
    }
}

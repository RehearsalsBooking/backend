<?php

/** @noinspection PhpUnusedLocalVariableInspection */

namespace Database\Seeders;

use App\Models\Band;
use App\Models\City;
use App\Models\Genre;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use App\Models\Organization\OrganizationRoomPrice;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;
use PDOException;

class DatabaseSeeder extends Seeder
{
    public const CITIES_COUNT = 3;
    public const ADMINS_COUNT = 5;
    public const USERS_COUNT = 20;
    public const INDIVIDUAL_REHEARSALS_COUNT = 200;
    public const BANDS_COUNT = 10;
    public const REHEARSALS_PER_BAND_COUNT = 400;
    public const BAND_MEMBERS_COUNT = 4;
    /**
     * @var User|User[]|Collection|Model|\Illuminate\Support\Collection|mixed
     */
    private mixed $admins;
    /**
     * @var Collection|Model|mixed
     */
    private mixed $organizations;
    /**
     * @var User|User[]|Collection|Model|\Illuminate\Support\Collection|mixed
     */
    private mixed $users;
    /**
     * @var Band|Band[]|Collection|Model|mixed
     */
    private mixed $bands;
    private mixed $userToLoginWith;
    private \Illuminate\Support\Collection $cities;

    public function run(): void
    {
        $this->command->info('creating admins');
        $this->admins = $this->createAdmins(self::ADMINS_COUNT);

        $this->command->info('creating cities');
        $this->cities = $this->createCities();

        $this->command->info('creating organizations');
        $this->organizations = $this->createOrganizations();

        $this->command->info('creating users');
        $this->users = $this->createUsers(self::USERS_COUNT);

        $this->command->info('creating prices and bands for organizations');
        $this->createPricesForOrganizations();

        $this->command->info('creating individual rehearsals');
        $this->createIndividualRehearsals(self::INDIVIDUAL_REHEARSALS_COUNT);

        $this->command->info('creating bands');
        $this->bands = $this->createBands(self::BANDS_COUNT);

        $this->command->info('adding members to bands');
        $this->addMembersToBands();

        $this->command->info('creating bands for logged in user');
        $this->createBandsForLoggedInUser();

        $this->command->info('creating band invites');
        $this->createBandInvites();

        $this->command->info('creating band rehearsals');
        $this->createBandRehearsals(self::REHEARSALS_PER_BAND_COUNT);
    }

    protected function createAdmins(int $count): \Illuminate\Support\Collection
    {
        return User::factory()->count($count)->create()->push($this->createUserToLoginWith());
    }

    private function createUserToLoginWith(): Collection|Model|User
    {
        return $this->userToLoginWith = User::factory()->create([
            'email' => 'test@rehearsals.com',
        ]);
    }

    protected function createOrganizations(): \Illuminate\Support\Collection
    {
        $createdOrganizations = collect();
        foreach ($this->admins as $admin) {
            $newOrganizations = Organization::factory()->count(2)->create(
                [
                    'owner_id' => $admin->id,
                    'city_id' => $this->cities->random(1)->first()->id
                ]
            );
            foreach ($newOrganizations as $newOrganization) {
                foreach (range(0, random_int(1, 2)) as $item) {
                    OrganizationRoom::factory()->create([
                        'organization_id' => $newOrganization->id
                    ]);
                }
            }
            $createdOrganizations->push($newOrganizations);
        }

        return $createdOrganizations->flatten();
    }

    protected function createUsers(int $count): \Illuminate\Support\Collection
    {
        return User::factory()->count($count)->create()->push($this->userToLoginWith);
    }

    protected function createPricesForOrganizations(): void
    {
        foreach (range(0, 6) as $dayOfWeek) {
            foreach ($this->organizations as $organization) {
                foreach ($organization->rooms as $room) {
                    OrganizationRoomPrice::factory()->create(
                        [
                            'organization_room_id' => $room->id,
                            'day' => $dayOfWeek,
                            'time' => new TimeRange('08:00', '19:00'),
                            'price' => 200,
                        ]
                    );
                    OrganizationRoomPrice::factory()->create(
                        [
                            'organization_room_id' => $room->id,
                            'day' => $dayOfWeek,
                            'time' => new TimeRange('19:00', '23:59'),
                            'price' => 300,
                        ]
                    );
                }
            }
        }
    }

    protected function createIndividualRehearsals(int $count): void
    {
        foreach (range(1, $count) as $_) {
            try {
                $individualRehearsal = Rehearsal::factory()->create(
                    [
                        'user_id' => $this->users->random()->id,
                        'organization_room_id' => $this->organizations->random()->rooms->random()->id,
                        'is_paid' => array_rand([true, false]),
                    ]
                );
            } catch (PDOException | QueryException) {
                // because rehearsal time is completely random
                // there is possible overlapping
                // so we just continue creating, if that occurs
                continue;
            }
        }
    }

    protected function createBands(int $count): Model|Collection
    {
        return Band::factory()
            ->has(Genre::factory()->count(3), 'genres')
            ->count($count)
            ->create();
    }

    private function addMembersToBands(): void
    {
        $this->bands->each(
            function (Band $band) {
                $this->users
                    ->random(self::BAND_MEMBERS_COUNT)
                    ->pluck('id')
                    ->push($band->admin_id)
                    ->each(
                        function (int $userId) use ($band) {
                            $band->addMember($userId);
                        }
                    );
            }
        );
    }

    protected function createBandInvites(): void
    {
        foreach ($this->bands as $band) {
            $band->invite(
                $this->users
                    ->whereNotIn('id', $band->members->fresh()->pluck('id')->toArray())
                    ->random()
                    ->email
            );
        }
    }

    /**
     * @param  int  $count
     */
    protected function createBandRehearsals(int $count): void
    {
        $this->bands->each(function ($band) use ($count) {
            foreach (range(1, $count) as $_) {
                try {
                    Rehearsal::factory()->create([
                        'organization_room_id' => $this->organizations->random()->rooms->random()->id,
                        'user_id' => $band->admin_id,
                        'band_id' => $band->id,
                        'is_paid' => array_rand([true, false]),
                    ]);
                } catch (PDOException | QueryException) {
                    // because rehearsal time is completely random
                    // there is possible overlapping
                    // so we just continue creating, if that occurs
                    continue;
                }
            }
        });
    }

    private function createBandsForLoggedInUser(): void
    {
        foreach (range(1, 2) as $_) {
            $bandForLoggedInUser = Band::factory()
                ->has(Genre::factory()->count(3), 'genres')
                ->create(['admin_id' => $this->userToLoginWith->id]);
            $this->users
                ->random(self::BAND_MEMBERS_COUNT)
                ->pluck('id')
                ->push($this->userToLoginWith->id)
                ->each(
                    function (int $userId) use ($bandForLoggedInUser) {
                        $bandForLoggedInUser->addMember($userId);
                    }
                );
        }
    }

    private function createCities(): \Illuminate\Support\Collection
    {
        return City::factory()->count(self::CITIES_COUNT)->create()->flatten();
    }
}

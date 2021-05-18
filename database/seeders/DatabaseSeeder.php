<?php

/** @noinspection PhpUnusedLocalVariableInspection */

namespace Database\Seeders;

use App\Models\Band;
use App\Models\Genre;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public const ADMINS_COUNT = 5;
    public const ORGANIZATIONS_COUNT = 5;
    public const USERS_COUNT = 20;
    public const INDIVIDUAL_REHEARSALS_COUNT = 50;
    public const BANDS_COUNT = 10;
    public const REHEARSALS_PER_BAND_COUNT = 100;
    public const BAND_MEMBERS_COUNT = 4;
    /**
     * @var User|User[]|Collection|Model|\Illuminate\Support\Collection|mixed
     */
    private $admins;
    /**
     * @var Collection|Model|mixed
     */
    private $organizations;
    /**
     * @var User|User[]|Collection|Model|\Illuminate\Support\Collection|mixed
     */
    private $users;
    /**
     * @var Band|Band[]|Collection|Model|mixed
     */
    private $bands;
    private User $userToLoginWith;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('creating admins');
        $this->admins = $this->createAdmins(self::ADMINS_COUNT);

        $this->command->info('creating organizations');
        $this->organizations = $this->createOrganizations(self::ORGANIZATIONS_COUNT);

        $this->command->info('creating users');
        $this->users = $this->createUsers(self::USERS_COUNT);

        $this->command->info('creating prices and bands for organizations');
        $this->createPricesAndBansForOrganizations();

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

    /**
     * @param  int  $count
     * @return User|User[]|Collection|Model|mixed
     */
    protected function createAdmins(int $count): \Illuminate\Support\Collection
    {
        return User::factory()->count($count)->create()->push($this->createUserToLoginWith());
    }

    private function createUserToLoginWith(): User
    {
        return $this->userToLoginWith = User::factory()->create([
            'email' => 'belamov@belamov.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * @param  int  $count
     * @return Collection|Model|mixed
     */
    protected function createOrganizations(int $count)
    {
        $organizations = [];
        foreach (range(1, $count) as $_) {
            $organizations[] = Organization::factory()->create(
                [
                    'owner_id' => $this->admins->random()->id,
                ]
            );
        }

        return collect($organizations);
    }

    /**
     * @param  int  $count
     * @return User|User[]|Collection|Model|mixed
     */
    protected function createUsers(int $count): \Illuminate\Support\Collection
    {
        return User::factory()->count($count)->create()->push($this->userToLoginWith);
    }

    protected function createPricesAndBansForOrganizations(): void
    {
        foreach (range(0, 6) as $dayOfWeek) {
            foreach ($this->organizations as $organization) {
                OrganizationPrice::factory()->create(
                    [
                        'organization_id' => $organization->id,
                        'day' => $dayOfWeek,
                        'time' => new TimeRange('08:00', '19:00'),
                        'price' => 200,
                    ]
                );
                OrganizationPrice::factory()->create(
                    [
                        'organization_id' => $organization->id,
                        'day' => $dayOfWeek,
                        'time' => new TimeRange('19:00', '23:59'),
                        'price' => 300,
                    ]
                );

                try {
                    OrganizationUserBan::create([
                        'organization_id' => $organization->id,
                        'user_id' => $this->users->random()->id,
                        'comment' => 'some reason to ban',
                    ]);
                } catch (PDOException | QueryException) {
                    // we may get already banned user
                    // in that case just continue creating
                    continue;
                }
            }
        }
    }

    /**
     * @param $count
     */
    protected function createIndividualRehearsals($count): void
    {
        foreach (range(1, $count) as $_) {
            try {
                $individualRehearsal = Rehearsal::factory()->create(
                    [
                        'user_id' => $this->users->random()->id,
                        'organization_id' => $this->organizations->random()->id,
                        'is_confirmed' => array_rand([true, false]),
                    ]
                );
                $individualRehearsal->registerUserAsAttendee();
            } catch (PDOException | QueryException) {
                // because rehearsal time is completely random
                // there is possible overlapping
                // so we just continue creating, if that occurs
                continue;
            }
        }
    }

    /**
     * @param  int  $count
     * @return Band|Band[]|Collection|Model|mixed
     */
    protected function createBands(int $count)
    {
        return Band::factory()
            ->has(Genre::factory()->count(3), 'genres')
            ->count($count)
            ->create();
    }

    private function addMembersToBands(): void
    {
        $this->bands->each(
            fn(Band $band) => $band->members()->sync(
                $this->users->random(self::BAND_MEMBERS_COUNT)->pluck('id')
            )
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
                        'organization_id' => $this->organizations->random()->id,
                        'user_id' => $band->admin_id,
                        'band_id' => $band->id,
                        'is_confirmed' => array_rand([true, false]),
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
        $bandForLoggedInUser = Band::factory()
            ->has(Genre::factory()->count(3), 'genres')
            ->create(['admin_id' => $this->userToLoginWith->id]);
        $bandForLoggedInUser->members()->sync([$this->userToLoginWith->id]);

        $bandForLoggedInUser = Band::factory()
            ->has(Genre::factory()->count(3), 'genres')
            ->create(['admin_id' => $this->userToLoginWith->id]);
        $bandForLoggedInUser->members()->sync([$this->userToLoginWith->id]);
    }
}

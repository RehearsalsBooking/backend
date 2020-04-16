<?php

/** @noinspection PhpUnusedLocalVariableInspection */

use App\Models\Band;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public const ADMINS_COUNT = 5;
    public const ORGANIZATIONS_COUNT = 5;
    public const USERS_COUNT = 20;
    public const INDIVIDUAL_REHEARSALS_COUNT = 50;
    public const BANDS_COUNT = 10;
    public const REHEARSALS_PER_BAND_COUNT = 500;
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

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->admins = $this->createAdmins(self::ADMINS_COUNT);

        $this->organizations = $this->createOrganizations(self::ORGANIZATIONS_COUNT);

        $this->users = $this->createUsers(self::USERS_COUNT);

        $this->createPricesAndBansForOrganizations();

        $this->createIndividualRehearsals(self::INDIVIDUAL_REHEARSALS_COUNT);

        $this->bands = $this->createBands(self::BANDS_COUNT);

        $this->addMembersToBands();

        $this->createBandInvites();

        $this->createBandRehearsals(self::REHEARSALS_PER_BAND_COUNT);
    }

    /**
     * @param  int  $count
     * @return User|User[]|Collection|Model|mixed
     */
    protected function createAdmins(int $count): \Illuminate\Support\Collection
    {
        return factory(User::class, $count)->create()->push($this->createUserToLoginWith());
    }

    private function createUserToLoginWith(): User
    {
        return factory(User::class)->create([
            'email' => 'belamov@belamov.com',
            'password' => bcrypt('password')
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
            $organizations[] = factory(Organization::class)->create(
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
        return factory(User::class, $count)->create();
    }

    protected function createPricesAndBansForOrganizations(): void
    {
        foreach (range(1, 7) as $dayOfWeek) {
            foreach ($this->organizations as $organization) {
                factory(OrganizationPrice::class)->create(
                    [
                        'organization_id' => $organization->id,
                        'day' => $dayOfWeek,
                        'time' => new TimeRange('00:00', '24:00'),
                    ]
                );

                try {
                    OrganizationUserBan::create([
                        'organization_id' => $organization->id,
                        'user_id' => $this->users->random()->id,
                        'comment' => 'some reason to ban',
                    ]);
                } catch (PDOException $e) {
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
                $individualRehearsal = factory(Rehearsal::class)->create(
                    [
                        'user_id' => $this->users->random()->id,
                        'organization_id' => $this->organizations->random()->id,
                        'is_confirmed' => array_rand([true, false]),
                    ]
                );
                $individualRehearsal->registerUserAsAttendee();
            } catch (PDOException $e) {
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
        return factory(Band::class, $count)->create();
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
                    factory(Rehearsal::class)->create([
                        'organization_id' => $this->organizations->random()->id,
                        'user_id' => $band->admin_id,
                        'band_id' => $band->id,
                        'is_confirmed' => array_rand([true, false]),
                    ]);
                } catch (PDOException $e) {
                    // because rehearsal time is completely random
                    // there is possible overlapping
                    // so we just continue creating, if that occurs
                    continue;
                }
            }
        });
    }
}

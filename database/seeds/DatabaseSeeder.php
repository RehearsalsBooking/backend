<?php /** @noinspection PhpUnusedLocalVariableInspection */

use App\Models\Band;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $admins = factory(User::class, 5)->create();

        $organizations = [];
        foreach (range(1, 5) as $_) {
            $organizations[] = factory(Organization::class)->create(['owner_id' => $admins->random()->id]);
        }
        $organizations = collect($organizations);

        $users = factory(User::class, 100)->create();

        foreach (range(1, 7) as $dayOfWeek) {
            foreach ($organizations as $organization) {
                try {
                    factory(OrganizationPrice::class)->create(
                        [
                            'organization_id' => $organization->id,
                            'day' => $dayOfWeek,
                            'time' => new TimeRange('00:00', '24:00'),
                        ]
                    );
                } catch (Throwable $throwable) {
                }
                $randomBannedUser = User::whereNotIn(
                    'id',
                    OrganizationUserBan::where('organization_id', $organization->id)->get('user_id')->toArray()
                )->inRandomOrder()->first()->id;
                OrganizationUserBan::create([
                    'organization_id' => $organization->id,
                    'user_id' => $randomBannedUser,
                    'comment' => 'some reason to ban'
                ]);
            }
        }

        //individual rehearsals
        try {
            $individualRehearsals = factory(Rehearsal::class, 50)->create(
                [
                    'user_id' => $users->random()->id,
                    'organization_id' => $organizations->random()->id,
                    'is_confirmed' => array_rand([true, false]),
                ]
            );
            $individualRehearsals->each(fn ($rehearsal) => $rehearsal->registerUserAsAttendee());
        } catch (Throwable $throwable) {
        }


        $bands = factory(Band::class, 10)->create();

        foreach ($bands as $band) {
            $band->members()->sync($users->random(4)->pluck('id'));
            $band->invite(
                $users
                    ->whereNotIn('id', $band->members->pluck('id')->toArray())
                    ->random()
            );
        }

        //band rehearsals
        $bands->each(static function ($band) use ($organizations) {
            if ($band->members->fresh()) {
                foreach (range(1, 50) as $_) {
                    try {
                        $bandRehearsal = factory(Rehearsal::class)->create([
                            'organization_id' => $organizations->random()->id,
                            'user_id' => $band->members->random()->id,
                            'band_id' => $band->id,
                            'is_confirmed' => array_rand([true, false]),
                        ]);
                        $bandRehearsal->registerBandMembersAsAttendees();
                    } catch (Throwable $throwable) {
                    }
                }
            }
        });
    }
}

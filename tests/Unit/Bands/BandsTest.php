<?php

namespace Tests\Unit\Bands;

use App\Models\Rehearsal;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BandsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function band_has_admin(): void
    {
        $bandAdmin = $this->createUser();

        $band = $this->createBandForUser($bandAdmin);

        $this->assertInstanceOf(User::class, $band->admin);
        $this->assertEquals(
            $bandAdmin->toArray(),
            $band->admin->toArray()
        );
    }

    /** @test */
    public function band_has_members(): void
    {
        $drummer = $this->createUser();
        $guitarist = $this->createUser();
        $vocalist = $this->createUser();

        $rockBand = $this->createBand();

        $rapBand = $this->createBand();

        $rockBandMembers = collect([$drummer, $guitarist, $vocalist]);
        $rapBandMembers = collect([$drummer, $vocalist]);

        $rockBand->members()->attach($rockBandMembers->pluck('id')->toArray());
        $rapBand->members()->attach($rapBandMembers->pluck('id')->toArray());

        $this->assertEquals(
            $rockBandMembers->pluck('id'),
            $rockBand->members->pluck('id')
        );

        $this->assertEquals(
            $rapBandMembers->pluck('id'),
            $rapBand->members->pluck('id')
        );
    }

    /** @test */
    public function band_has_rehearsals(): void
    {
        $band = $this->createBandForUser($this->createUser());

        $bandRehearsalsCount = 5;

        $bandRehearsals = factory(Rehearsal::class, $bandRehearsalsCount)->create([
            'band_id' => $band->id
        ]);

        $this->assertEquals(
            $bandRehearsalsCount,
            $band->rehearsals()->count()
        );

        $this->assertEquals(
            $bandRehearsals->pluck('id')->toArray(),
            $band->rehearsals->pluck('id')->toArray()
        );
    }

    /** @test */
    public function band_has_many_invites_for_users(): void
    {
        $band = $this->createBand();

        $invitedUsersCount = 3;
        $invitedUsers = $this->createUsers($invitedUsersCount);

        $invitedUsers->each(static function (User $user) use ($band) {
            $band->invite($user);
        });

        $this->assertEquals($invitedUsersCount, $band->invitedUsers()->count());
        $this->assertInstanceOf(User::class, $band->invitedUsers->first());
        $this->assertEquals($invitedUsers->pluck('id'), $band->invitedUsers->pluck('id'));
    }


    /** @test */
    public function band_has_rehearsals_in_future(): void
    {
        $band = $this->createBand();

        $this->createRehearsalForBandInThePast($band);
        $rehearsalInFuture = $this->createRehearsalForBandInFuture($band);

        $this->assertEquals([$rehearsalInFuture->id], $band->futureRehearsals->pluck('id')->toArray());
        $this->assertEquals(1, $band->futureRehearsals()->count());
    }
}

<?php

namespace Tests\Unit\Bands;

use App\Models\Invite;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            $bandAdmin->id,
            $band->admin->id
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

        $this->createBandMembership($drummer, $rockBand);
        $this->createBandMembership($guitarist, $rockBand);
        $this->createBandMembership($vocalist, $rockBand);

        $this->createBandMembership($drummer, $rapBand);
        $this->createBandMembership($vocalist, $rapBand);

        $expectedBandMembers = [$drummer->id, $guitarist->id, $vocalist->id];
        $actualBandMembers = $rockBand->members->pluck('users.id')->toArray();
        $this->assertEquals(
            sort($expectedBandMembers),
            sort($actualBandMembers)
        );

        $expectedBandMembers = [$drummer->id, $vocalist->id];
        $actualBandMembers = $rapBand->members->pluck('users.id')->toArray();
        $this->assertEquals(
            sort($expectedBandMembers),
            sort($actualBandMembers)
        );
    }

    /** @test */
    public function band_has_rehearsals(): void
    {
        $band = $this->createBandForUser($this->createUser());

        $bandRehearsalsCount = 5;

        $bandRehearsals = Rehearsal::factory()->count($bandRehearsalsCount)->create([
            'band_id' => $band->id,
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
            $band->invite($user->email);
        });

        $this->assertEquals($invitedUsersCount, $band->invites()->count());
        $this->assertInstanceOf(Invite::class, $band->invites->first());
        $this->assertEquals($invitedUsers->pluck('email'), $band->invites->pluck('email'));
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

    /** @test */
    public function band_has_genres(): void
    {
        $band = $this->createBand();

        $rockGenre = $this->createGenre();
        $rapGenre = $this->createGenre();

        $band->genres()->attach($rockGenre);
        $band->genres()->attach($rapGenre);

        $this->assertEquals(2, $band->fresh()->genres()->count());
        $this->assertEquals([$rockGenre->id, $rapGenre->id], $band->genres->pluck('id')->toArray());
    }
}

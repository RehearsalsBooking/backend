<?php

namespace Tests\Unit\Users;

use App\Models\Band;
use App\Models\Organization\Organization;
use App\Models\Rehearsal;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_have_organizations(): void
    {
        $user = $this->createUser();

        $this->createOrganization([
            'owner_id' => $user->id,
        ]);

        $this->assertInstanceOf(Collection::class, $user->organizations);
        $this->assertInstanceOf(Organization::class, $user->organizations->first());
    }

    /** @test */
    public function user_can_be_admin_in_multiple_bands(): void
    {
        $user = $this->createUser();

        $numberOfUsersBands = 5;

        $usersBands = factory(Band::class, $numberOfUsersBands)->create([
            'admin_id' => $user->id,
        ]);

        $this->assertEquals($numberOfUsersBands, $user->createdBands()->count());
        $this->assertEquals(
            $usersBands->pluck('id')->toArray(),
            $user->createdBands->pluck('id')->toArray()
        );
    }

    /** @test */
    public function users_can_participate_in_multiple_bands(): void
    {
        $drummer = $this->createUser();
        $guitarist = $this->createUser();

        /** @var Band $rockBand */
        $rockBand = factory(Band::class)->create();

        /** @var Band $rapBand */
        $rapBand = factory(Band::class)->create();

        /** @var Band $rapBand */
        $popBand = factory(Band::class)->create();

        $drummersBands = collect([$rockBand, $rapBand, $popBand]);
        $guitaristsBands = collect([$rockBand, $popBand]);

        $drummer->bands()->attach($drummersBands->pluck('id'));
        $guitarist->bands()->attach($guitaristsBands->pluck('id'));

        $this->assertEquals(
            $drummersBands->pluck('id'),
            $drummer->bands->pluck('id')
        );

        $this->assertEquals(
            $guitaristsBands->pluck('id'),
            $guitarist->bands->pluck('id')
        );
    }

    /** @test */
    public function user_can_attend_to_multiple_rehearsals(): void
    {
        $user = $this->createUser();

        $attendingRehearsalsCount = 5;

        $rehearsals = factory(Rehearsal::class, $attendingRehearsalsCount)
            ->create()
            ->each(static function ($rehearsal) use ($user) {
                DB::table('rehearsal_user')
                    ->insert([
                        'user_id' => $user->id,
                        'rehearsal_id' => $rehearsal->id,
                    ]);
            });

        $this->assertEquals($attendingRehearsalsCount, $user->rehearsals()->count());
        $this->assertInstanceOf(Rehearsal::class, $user->rehearsals->first());
        $this->assertEquals($rehearsals->pluck('id'), $user->rehearsals->pluck('id'));
    }

    /** @test */
    public function user_has_multiple_invites(): void
    {
        $user = $this->createUser();

        $bandsThatInvitedUserCount = 3;
        $bandsThatInvitedUser = factory(Band::class, $bandsThatInvitedUserCount)->create();

        $bandsThatInvitedUser->each(static function (Band $band) use ($user) {
            $band->invite($user);
        });

        $this->assertEquals($bandsThatInvitedUserCount, $user->invites()->count());
        $this->assertInstanceOf(Band::class, $user->invites->first());
        $this->assertEquals($bandsThatInvitedUser->pluck('id'), $user->invites->pluck('id'));
    }

    /** @test */
    public function user_has_favorite_organizations(): void
    {
        $user = $this->createUser();

        $favoriteOrganizations = $this->createOrganizations([], 3);

        $organizationsIds = $favoriteOrganizations->pluck('id')->toArray();
        $user->favoriteOrganizations()->sync($organizationsIds);

        $this->assertEquals($user->favoriteOrganizations()->count(), $favoriteOrganizations->count());
        $this->assertInstanceOf(Organization::class, $user->favoriteOrganizations->first());
    }
}

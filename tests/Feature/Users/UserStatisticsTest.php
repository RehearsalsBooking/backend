<?php

namespace Tests\Feature\Users;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStatisticsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_correct_rehearsal_count(): void
    {
        $user = $this->createUser();
        $this->createRehearsalForUserInFuture($user);
        $this->createRehearsal(startsAt: now()->setHour(10)->hour, endsAt: now()->setHour(11)->hour, user: $user);
        $this->createRehearsalForUserInPast($user);
        $this->createRehearsalForUserInPast($user);

        $this->createRehearsalForUserInPast($this->createUser());
        $this->createRehearsalForUserInFuture($this->createUser());

        $response = $this->json('get', route('users.statistics', [$user]));

        $response->assertOk();

        $this->assertEquals(2, $response->json('rehearsals_count'));
    }

    /** @test */
    public function it_fetches_users_registration_date(): void
    {
        $user = $this->createUser();

        $response = $this->json('get', route('users.statistics', [$user]));
        $response->assertOk();

        $this->assertEquals($user->created_at->toISOString(), $response->json('registered_at'));
    }

    /** @test */
    public function it_fetches_users_roles(): void
    {
        $user = $this->createUser();

        $roleGuitarist = 'guitarist';
        $roleVocal = 'vocal';

        $band = $this->createBand();
        $band->addMember($user->id, [$roleGuitarist]);

        $band = $this->createBand();
        $band->addMember($user->id, [$roleVocal, $roleGuitarist]);

        $response = $this->json('get', route('users.statistics', [$user]));
        $response->assertOk();

        $this->assertEquals([$roleGuitarist, $roleVocal], $response->json('roles'));
    }

    /** @test */
    public function it_fetches_users_bands_count(): void
    {
        $user = $this->createUser();

        $band = $this->createBand();
        $band->addMember($user->id);

        $band = $this->createBand();
        $band->addMember($user->id);

        $response = $this->json('get', route('users.statistics', [$user]));
        $response->assertOk();

        $this->assertEquals(2, $response->json('bands_count'));
    }
}

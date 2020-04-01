<?php

namespace Tests\Feature\Bands;

use App\Models\Band;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class BandsRegistrationTest
 * @property User $bandAdmin
 * @property Band $band
 * @package Tests\Feature\Bands
 */
class BandMembersDeleteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Band $band;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bandAdmin = $this->createUser();
        $this->band = $this->createBandForUser($this->bandAdmin);
    }

    /** @test */
    public function unauthenticated_user_cannot_delete_a_band(): void
    {
        $this->json('delete', route('bands.members.delete', [1, 1]))->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function some_other_user_cannot_delete_member_of_a_band(): void
    {
        $bandMembers = $this->createUsers(2);
        $this->band->members()->saveMany($bandMembers);
        $userIdToRemoveFromBand = $this->band->members()->inRandomOrder()->first(['id'])->id;

        $this->actingAs($this->createUser());

        $this->json('delete', route('bands.members.delete', [$this->band->id, $userIdToRemoveFromBand]))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function deleting_member_should_be_in_given_band(): void
    {
        $bandMembers = $this->createUsers(2);
        $this->band->members()->saveMany($bandMembers);
        $userIdToRemoveFromBand = $this->band->members()->inRandomOrder()->first(['id'])->id;

        $adminOfAnotherBand = $this->createUser();
        $anotherBand = $this->createBandForUser($adminOfAnotherBand);

        $this->actingAs($adminOfAnotherBand);

        $this->json('delete', route('bands.members.delete', [$anotherBand->id, $userIdToRemoveFromBand]))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

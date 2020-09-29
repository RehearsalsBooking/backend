<?php

namespace Tests\Feature\Rehearsals;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class RehearsalsFilterTest.
 */
class RehearsalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_fetch_rehearsals(): void
    {
        $rehearsals = Rehearsal::factory()->count(5)->create();

        $this->assertEquals(5, Rehearsal::count());

        $response = $this->get(route('rehearsals.list'));
        $response->assertOk();

        $data = $response->json();

        $this->assertCount(5, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection($rehearsals)->response()->getData(true),
            $data
        );
    }

    /** @test */
    public function user_can_see_valid_information_about_his_participation_in_rehearsal(): void
    {
        $user = $this->createUser();
        $band = $this->createBand();

        $band->addMember($user->id);

        $rehearsalOfSomeOtherUser = $this->createRehearsalForUserInFuture();
        $this->createRehearsalForBandInFuture($band);
        $this->createRehearsalForUserInFuture($user);

        $response = $this->get(route('rehearsals.list'));
        $response->assertOk();

        $data = $response->json('data');

        foreach ($data as $rehearsal) {
            $this->assertFalse($rehearsal['is_participant']);
        }

        $this->actingAs($user);
        $response = $this->get(route('rehearsals.list'));
        $response->assertOk();

        $data = $response->json('data');

        $this->assertCount(3, $data);

        foreach ($data as $rehearsal) {
            if ($rehearsal['id'] === $rehearsalOfSomeOtherUser->id) {
                $this->assertFalse($rehearsal['is_participant']);
            } else {
                $this->assertTrue($rehearsal['is_participant']);
            }
        }
    }

    /** @test */
    public function rehearsals_doesnt_contain_private_information(): void
    {
        $rehearsal = $this->createRehearsal(1, 2);

        $response = $this->get(route('rehearsals.list'));
        $response->assertOk();

        $data = $response->json('data');

        $this->assertEquals(
            [
                'id' => $rehearsal->id,
                'starts_at' => $rehearsal->time->from()->toDateTimeString(),
                'ends_at' => $rehearsal->time->to()->toDateTimeString(),
                'is_participant' => false,
            ],
            $data[0]
        );
    }
}

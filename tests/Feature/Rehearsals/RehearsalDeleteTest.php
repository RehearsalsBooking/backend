<?php

namespace Tests\Feature\Rehearsals;

use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthorized_users_cant_make_request_to_delete_rehearsal(): void
    {
        $this->json('delete', route('rehearsals.delete', 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_can_delete_its_rehearsal(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $rehearsal = $this->createRehearsalForUser($user);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $response = $this->json('delete', route('rehearsals.delete', $rehearsal->id));

        $response->assertOk();

        $this->assertDatabaseMissing('rehearsals', $rehearsal->toArray());
    }

    /** @test */
    public function user_cannot_delete_rehearsal_that_he_didnt_book(): void
    {
        $john = $this->createUser();
        $max = $this->createUser();
        $this->actingAs($max);

        $johnsRehearsal = $this->createRehearsalForUser($john);

        $this->assertDatabaseHas('rehearsals', $johnsRehearsal->toArray());

        $response = $this->json('delete', route('rehearsals.delete', $johnsRehearsal->id));

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('rehearsals', $johnsRehearsal->toArray());
    }

    /** @test */
    public function it_responds_with_404_when_user_deletes_unknown_rehearsal(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $this->assertDatabaseMissing('rehearsals', ['id' => 1000]);
        $response = $this->json('delete', route('rehearsals.delete', 1000));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @param User $user
     * @return Rehearsal
     */
    private function createRehearsalForUser(User $user): Rehearsal
    {
        return factory(Rehearsal::class)->create([
            'user_id' => $user->id
        ]);
    }
}

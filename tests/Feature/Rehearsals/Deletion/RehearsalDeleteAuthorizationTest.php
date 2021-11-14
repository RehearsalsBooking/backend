<?php

namespace Tests\Feature\Rehearsals\Deletion;

use App\Models\Rehearsal;
use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalDeleteAuthorizationTest extends TestCase
{
    /** @test */
    public function unauthorized_users_cant_make_request_to_delete_rehearsal(): void
    {
        $this->json('delete', route('rehearsals.delete', 1))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_cannot_delete_rehearsal_that_he_didnt_book(): void
    {
        $john = $this->createUser();
        $max = $this->createUser();
        $this->actingAs($max);

        $johnsRehearsal = $this->createRehearsalForUser($john);

        $this->assertDatabaseHas(Rehearsal::class, $johnsRehearsal->getAttributes());

        $response = $this->json('delete', route('rehearsals.delete', $johnsRehearsal->id));

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas(Rehearsal::class, $johnsRehearsal->getAttributes());
    }
}

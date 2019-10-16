<?php

namespace Tests\Feature\Rehearsals\Deletion;

use App\Models\Rehearsal;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalDeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_delete_his_rehearsal(): void
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
    public function user_cannot_delete_rehearsal_that_already_began(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $rehearsal = factory(Rehearsal::class)->create([
            'user_id' => $user->id,
            'starts_at' => Carbon::now()->subHour(),
            'ends_at' => Carbon::now()->addHour(),
        ]);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $this->json('delete', route('rehearsals.delete', $rehearsal->id))->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());
    }

    /** @test */
    public function user_cannot_delete_rehearsal_that_already_finished(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $rehearsal = factory(Rehearsal::class)->create([
            'user_id' => $user->id,
            'starts_at' => Carbon::now()->subHours(3),
            'ends_at' => Carbon::now()->subHours(2),
        ]);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $this->json('delete', route('rehearsals.delete', $rehearsal->id))->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());
    }
}

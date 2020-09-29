<?php

namespace Tests\Feature\Rehearsals\Deletion;

use App\Models\Rehearsal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class RehearsalDeleteValidationTest.
 *
 * @property $user User
 */
class RehearsalDeleteValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    /** @test */
    public function it_responds_with_404_when_user_deletes_unknown_rehearsal(): void
    {
        $this->assertDatabaseMissing('rehearsals', ['id' => 1000]);
        $response = $this->json('delete', route('rehearsals.delete', 1000));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function user_cannot_delete_rehearsal_that_already_began(): void
    {
        $rehearsal = Rehearsal::factory()->create([
            'user_id' => $this->user->id,
            'time' => $this->getTimestampRange(Carbon::now()->subHour(), Carbon::now()->addHour()),
        ]);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $this->json('delete', route('rehearsals.delete', $rehearsal->id))->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());
    }

    /** @test */
    public function user_cannot_delete_rehearsal_that_already_finished(): void
    {
        $rehearsal = Rehearsal::factory()->create([
            'user_id' => $this->user->id,
            'time' => $this->getTimestampRange(Carbon::now()->subHours(3), Carbon::now()->subHours(2)),
        ]);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());

        $this->json('delete', route('rehearsals.delete', $rehearsal->id))->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('rehearsals', $rehearsal->toArray());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->actingAs($this->user);
    }
}

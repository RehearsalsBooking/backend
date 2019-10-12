<?php

namespace Tests\Feature\Rehearsals\Deletion;

use App\Models\Rehearsal;
use App\Models\User;
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
}

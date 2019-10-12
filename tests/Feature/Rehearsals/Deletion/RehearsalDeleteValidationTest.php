<?php

namespace Tests\Feature\Rehearsals\Deletion;

use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalDeleteValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_responds_with_404_when_user_deletes_unknown_rehearsal(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $this->assertDatabaseMissing('rehearsals', ['id' => 1000]);
        $response = $this->json('delete', route('rehearsals.delete', 1000));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

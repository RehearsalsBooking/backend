<?php

namespace Tests\Feature\Rehearsals;

use App\Http\Resources\RehearsalDetailedResource;
use App\Models\Rehearsal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRehearsalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_users_rehearsals(): void
    {
        $max = $this->createUser();
        $john = $this->createUser();

        $maxesRehearsal = $this->createRehearsalForUser($max);
        $this->createRehearsalForUser($john);

        $this->assertEquals(2, Rehearsal::count());

        $response = $this->get(route('users.rehearsals', [$max->id]));
        $response->assertOk();

        $data = $response->json();

        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            RehearsalDetailedResource::collection([$maxesRehearsal])->response()->getData(true),
            $data
        );
    }
}

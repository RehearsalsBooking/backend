<?php

namespace Tests\Feature\Rehearsals;

use App\Http\Resources\RehearsalDetailedResource;
use App\Models\Rehearsal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRehearsalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rehearsal::truncate();
    }

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

    /** @test */
    public function it_orders_rehearsals_by_time(): void
    {
        $user = $this->createUser();
        $secondRehearsal = $this->createRehearsal(18, 20, user: $user);
        $firstRehearsal = $this->createRehearsal(9, 11, user: $user);

        $response = $this->get(route('users.rehearsals', [$user]));
        $response->assertOk();

        $this->assertCount(2, $response->json('data'));
        $this->assertEquals($firstRehearsal->id, $response->json('data.0.id'));
        $this->assertEquals($secondRehearsal->id, $response->json('data.1.id'));
    }
}

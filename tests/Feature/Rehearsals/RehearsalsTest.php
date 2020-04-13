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
        $rehearsals = factory(Rehearsal::class, 5)->create();

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
            ],
            $data[0]
        );
    }
}

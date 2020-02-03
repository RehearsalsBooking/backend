<?php

namespace Tests\Feature\Rehearsals;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class RehearsalsFilterTest
 * @package Tests\Feature\Rehearsals
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
}

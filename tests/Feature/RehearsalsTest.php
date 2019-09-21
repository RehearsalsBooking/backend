<?php

namespace Tests\Feature;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Organization;
use App\Models\Rehearsal;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_fetch_rehearsals_of_organization(): void
    {
        $organization = factory(Organization::class)->create();
        $rehearsals = factory(Rehearsal::class, 5)->create(['organization_id' => $organization->id]);

        $response = $this->get(route('organizations.rehearsals', $organization->id));
        $response->assertOk();

        $data = $response->json();

        $this->assertCount(5, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection($rehearsals)->response()->getData(true),
            $data
        );
    }

    /** @test */
    public function it_responds_with_404_when_client_provided_unknown_organization(): void
    {
        $this->get(route('organizations.rehearsals', 10000))->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get(route('organizations.rehearsals', 'asd'))->assertStatus(Response::HTTP_NOT_FOUND);
    }
}

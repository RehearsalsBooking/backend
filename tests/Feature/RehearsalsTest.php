<?php

namespace Tests\Feature;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_fetch_rehearsals_of_organization(): void
    {
        $organization = $this->createOrganization();
        $rehearsals = factory(Rehearsal::class, 5)->create(['organization_id' => $organization->id]);

        $response = $this->get(route('organizations.rehearsals.list', $organization->id));
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
        $this->get(route('organizations.rehearsals.list', 10000))->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get(route('organizations.rehearsals.list', 'asd'))->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function user_can_book_a_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $user = $this->createUser();

        $this->actingAs($user);

        $this->assertEquals(0, Rehearsal::count());

        $rehearsalStart = $this->generateRandomDate();

        /**
         * @var $rehearsalEnd Carbon
         */
        $rehearsalEnd = $rehearsalStart->copy()->addHours(2);
        $response = $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $rehearsalStart->toDateTimeString(),
                'ends_at' => $rehearsalEnd->toDateTimeString()
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, Rehearsal::count());

        $createdRehearsal = Rehearsal::first();
        $this->assertEquals($rehearsalStart, $createdRehearsal->starts_at);
        $this->assertEquals($rehearsalEnd, $createdRehearsal->ends_at);
        $this->assertEquals($user->id, $createdRehearsal->user->id);
        $this->assertEquals($organization->id, $createdRehearsal->organization->id);
        $this->assertEquals(
            (new RehearsalResource($createdRehearsal))->response()->getData(true),
            $response->json()
        );
    }
}

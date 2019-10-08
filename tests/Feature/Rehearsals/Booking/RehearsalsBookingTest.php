<?php

namespace Tests\Feature\Rehearsals\Booking;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalsBookingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_book_individual_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $user = $this->createUser();

        $this->actingAs($user);

        $this->assertEquals(0, Rehearsal::count());

        $rehearsalTime = $this->getRehearsalTime();

        $response = $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            $rehearsalTime
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, Rehearsal::count());

        $createdRehearsal = Rehearsal::first();
        $this->assertEquals(
            $rehearsalTime,
            [
                'starts_at' => $createdRehearsal->starts_at->toDateTimeString(),
                'ends_at' => $createdRehearsal->ends_at->toDateTimeString()
            ]
        );
        $this->assertEquals($user->id, $createdRehearsal->user->id);
        $this->assertEquals($organization->id, $createdRehearsal->organization->id);
        $this->assertEquals(null, $createdRehearsal->band);
        $this->assertEquals(
            (new RehearsalResource($createdRehearsal))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function user_can_book_a_rehearsal_on_behalf_of_his_band(): void
    {
        $organization = $this->createOrganization();
        $user = $this->createUser();

        $band = $this->createBandForUser($user);

        $this->actingAs($user);

        $this->assertEquals(0, Rehearsal::count());

        $rehearsalTime = $this->getRehearsalTime();

        $response = $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            array_merge(['band_id' => $band->id], $rehearsalTime)
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, Rehearsal::count());

        $createdRehearsal = Rehearsal::first();
        $this->assertEquals(
            $rehearsalTime,
            [
                'starts_at' => $createdRehearsal->starts_at->toDateTimeString(),
                'ends_at' => $createdRehearsal->ends_at->toDateTimeString()
            ]
        );
        $this->assertEquals($user->id, $createdRehearsal->user->id);
        $this->assertEquals($organization->id, $createdRehearsal->organization->id);
        $this->assertEquals($band->id, $createdRehearsal->band->id);
        $this->assertEquals(
            (new RehearsalResource($createdRehearsal))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function when_user_books_rehearsal_its_status_is_set_to_unconfirmed(): void
    {
        $organization = $this->createOrganization();

        $this->actingAs($this->createUser());


        $this->assertEquals(0, Rehearsal::count());

        $response = $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            $this->getRehearsalTime()
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $createdRehearsal = Rehearsal::first();

        $this->assertFalse($createdRehearsal->is_confirmed);
    }
}

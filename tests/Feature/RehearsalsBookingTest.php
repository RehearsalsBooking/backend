<?php

namespace Tests\Feature;

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
    public function user_can_book_a_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $user = $this->createUser();

        $this->actingAs($user);

        $this->assertEquals(0, Rehearsal::count());

        /**
         * @var $rehearsalStart Carbon
         */
        $rehearsalStart = Carbon::now()->addHour();

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
        $this->assertEquals($rehearsalStart, $createdRehearsal->starts_at->toDateTimeString());
        $this->assertEquals($rehearsalEnd, $createdRehearsal->ends_at->toDateTimeString());
        $this->assertEquals($user->id, $createdRehearsal->user->id);
        $this->assertEquals($organization->id, $createdRehearsal->organization->id);
        $this->assertEquals(
            (new RehearsalResource($createdRehearsal))->response()->getData(true),
            $response->json()
        );
    }

    /** @test */
    public function when_user_books_rehearsal_its_status_is_set_to_unverified(): void
    {
        $organization = $this->createOrganization();

        $this->actingAs($this->createUser());

        /**
         * @var $rehearsalStart Carbon
         */
        $rehearsalStart = Carbon::now()->addHour();

        /**
         * @var $rehearsalEnd Carbon
         */
        $rehearsalEnd = $rehearsalStart->copy()->addHours(2);

        $this->assertEquals(0, Rehearsal::count());

        $response = $this->json(
            'post',
            route('organizations.rehearsals.create', $organization->id),
            [
                'starts_at' => $rehearsalStart->toDateTimeString(),
                'ends_at' => $rehearsalEnd->toDateTimeString()
            ]
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $createdRehearsal = Rehearsal::first();

        $this->assertFalse($createdRehearsal->is_confirmed);
    }

}

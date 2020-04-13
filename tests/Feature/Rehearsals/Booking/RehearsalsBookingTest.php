<?php

namespace Tests\Feature\Rehearsals\Booking;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Organization\OrganizationUserBan;
use App\Models\Rehearsal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalsBookingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_book_individual_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $this->createPricesForOrganization($organization);
        $user = $this->createUser();

        $this->actingAs($user);

        $this->assertEquals(0, Rehearsal::count());

        $params = $rehearsalTime = $this->getRehearsalTime();
        $params['organization_id'] = $organization->id;

        $response = $this->json(
            'post',
            route('rehearsals.create'),
            $params
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, Rehearsal::count());

        $createdRehearsal = Rehearsal::first();
        $this->assertEquals(
            $rehearsalTime,
            [
                'starts_at' => $createdRehearsal->time->from()->toDateTimeString(),
                'ends_at' => $createdRehearsal->time->to()->toDateTimeString(),
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
        $this->createPricesForOrganization($organization);
        $user = $this->createUser();

        $band = $this->createBandForUser($user);

        $this->actingAs($user);

        $this->assertEquals(0, Rehearsal::count());

        $params = $rehearsalTime = $this->getRehearsalTime();
        $params['band_id'] = $band->id;
        $params['organization_id'] = $organization->id;

        $response = $this->json(
            'post',
            route('rehearsals.create'),
            $params
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(1, Rehearsal::count());

        $createdRehearsal = Rehearsal::first();
        $this->assertEquals(
            $rehearsalTime,
            [
                'starts_at' => $createdRehearsal->time->from()->toDateTimeString(),
                'ends_at' => $createdRehearsal->time->to()->toDateTimeString(),
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
        $this->createPricesForOrganization($organization);

        $this->actingAs($this->createUser());

        $this->assertEquals(0, Rehearsal::count());

        $params = $this->getRehearsalTime();
        $params['organization_id'] = $organization->id;

        $response = $this->json(
            'post',
            route('rehearsals.create'),
            $params
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $createdRehearsal = Rehearsal::first();

        $this->assertFalse($createdRehearsal->is_confirmed);
    }

    /** @test */
    public function banned_users_cannot_book_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $this->createPricesForOrganization($organization);
        $user = $this->createUser();

        OrganizationUserBan::create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        $this->assertEquals($user->id, $organization->bannedUsers->first()->id);

        $this->actingAs($user);

        $params = $this->getRehearsalTime();
        $params['organization_id'] = $organization->id;

        $this->json(
            'post',
            route('rehearsals.create'),
            $params
        )->assertStatus(Response::HTTP_FORBIDDEN);

        $params['band_id'] = $this->createBandForUser($user)->id;

        $this->json(
            'post',
            route('rehearsals.create'),
            $params
        )->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertEquals(0, Rehearsal::count());
    }
}

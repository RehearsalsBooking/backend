<?php

namespace Tests\Feature\Prices;

use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use Belamov\PostgresRange\Ranges\TimeRange;
use Tests\TestCase;

/**
 * Class RehearsalsFilterTest.
 */
class PricesTest extends TestCase
{
    private OrganizationRoom $organizationRoom;
    private string $endpoint;

    protected function setUp(): void
    {
        parent::setUp();
        $this->endpoint = route('rehearsals.price');

        $organization = $this->createOrganization();
        $this->organizationRoom = $this->createOrganizationRoom($organization);

        // prices at monday
        // 10-14 100
        // 14-20 200
        // 20-00 300
        $this->organizationRoom->prices()->createMany([
            [
                'day' => 1,
                'price' => 100,
                'time' => new TimeRange('10:00', '14:00'),
            ],
            [
                'day' => 1,
                'price' => 200,
                'time' => new TimeRange('14:00', '20:00'),
            ],
            [
                'day' => 1,
                'price' => 300,
                'time' => new TimeRange('20:00', '24:00'),
            ],
        ]);
    }

    /**
     * @test
     * price calculation logic is tested in tests/Unit/Rehearsals/RehearsalPriceCalculationTest.php
     */
    public function it_returns_correct_price(): void
    {
        // prices at monday
        // 10-14 100
        // 14-20 200
        // 20-00 300
        $response = $this->json(
            'get',
            $this->endpoint,
            [
                'starts_at' => $this->getDateTimeAtMonday(10, 00),
                'ends_at' => $this->getDateTimeAtMonday(11, 30),
                'organization_room_id' => $this->organizationRoom->id
            ]
        );
        $response->assertOk();

        $fetchedPrice = $response->json();
        $this->assertEquals(100 * 1.5, $fetchedPrice);
    }

    /** @test */
    public function it_responses_with_422_when_unknown_organization_room_is_given(): void
    {
        $this->getJson(
            $this->endpoint,
            [
                'starts_at' => $this->getDateTimeAtMonday(10, 00),
                'ends_at' => $this->getDateTimeAtMonday(11, 30),
                'organization_room_id' => 1000
            ]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('organization_room_id');
        $this->getJson(
            $this->endpoint,
            [
                'starts_at' => $this->getDateTimeAtMonday(10, 00),
                'ends_at' => $this->getDateTimeAtMonday(11, 30),
                'organization_room_id' => 'unknown'
            ]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('organization_room_id');
    }

    /** @test */
    public function it_calculates_price_for_rehearsal_even_when_time_is_booked_by_this_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization);

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(10, 12, $room, null, false, $user);

        $this->assertEquals(1, Rehearsal::count());

        $newRehearsalStartTime = $rehearsal->time->from()->subHour();
        $newRehearsalEndTime = $rehearsal->time->to()->addHour();

        $response = $this->json(
            'get',
            $this->endpoint,
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString(),
                'rehearsal_id' => $rehearsal->id,
                'organization_room_id' => $room->id
            ]
        );

        $response->assertOk();
    }
}

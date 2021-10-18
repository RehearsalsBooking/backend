<?php

namespace Tests\Feature\Prices;

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use App\Models\Rehearsal;
use Belamov\PostgresRange\Ranges\TimeRange;
use Carbon\Carbon;
use Tests\Feature\Rehearsals\Booking\ValidatesRehearsalTime;
use Tests\TestCase;

/**
 * Class RehearsalsFilterTest.
 */
class PricesTest extends TestCase
{
    use ValidatesRehearsalTime;


    private OrganizationRoom $organizationRoom;
    private $endpoint = 'rooms.price';

    protected function setUp(): void
    {
        parent::setUp();

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
            route($this->endpoint, $this->organizationRoom),
            [
                'starts_at' => $this->getDateTimeAtMonday(10, 00),
                'ends_at' => $this->getDateTimeAtMonday(11, 30),
            ]
        );
        $response->assertOk();

        $fetchedPrice = $response->json();
        $this->assertEquals(100 * 1.5, $fetchedPrice);
    }

    private function getDateTimeAtMonday(int $hour, int $minute): Carbon
    {
        return Carbon::create(2030, 1, 7, $hour, $minute);
    }

    /** @test */
    public function it_responses_with_404_when_unknown_organization_room_is_given(): void
    {
        $this->get(route($this->endpoint, 1000),
            [
                'starts_at' => $this->getDateTimeAtMonday(10, 00),
                'ends_at' => $this->getDateTimeAtMonday(11, 30),
            ])->assertNotFound();
        $this->get(route('rooms.price', 'unknown'),
            [
                'starts_at' => $this->getDateTimeAtMonday(10, 00),
                'ends_at' => $this->getDateTimeAtMonday(11, 30),
            ])->assertNotFound();
    }

    /**
     * @test
     * @dataProvider getDataWithInvalidFormat
     * @param $data
     * @param $keyWithError
     */
    public function it_responds_with_validation_error_when_user_provided_invalid_time_parameters(
        $data,
        $keyWithError
    ): void {
        $room = $this->createOrganizationRoom($this->createOrganization());

        $response = $this->json(
            'get',
            route('rooms.price', $room),
            $data
        );

        $response->assertJsonValidationErrors($keyWithError);
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_provided_time_when_organization_room_is_closed(): void
    {
        $room = $this->createOrganizationRoom($this->createOrganization());

        $this->performTestWhenOrganizationIsClosed(
            'get',
            route('rooms.price', $room),
            $room,
        );
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_provided_incorrect_rehearsal_duration(): void
    {
        $room = $this->createOrganizationRoom($this->createOrganization());

        $this->performTestsWhenUserProvidedIncorrectRehearsalDuration(
            'get',
            route('rooms.price', $room),
            $room
        );
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_tries_to_book_rehearsal_longer_than_24_hours(): void
    {
        $room = $this->createOrganizationRoom($this->createOrganization());

        $this->performTestsWhenUserProvidesRehearsalTimeLongerThan24Hours(
            'get',
            route('rooms.price', $room),
            $room,
        );
    }

    /** @test */
    public function it_responds_with_validation_error_when_user_selected_unavailable_time(): void
    {
        $room = $this->createOrganizationRoom($this->createOrganization());

        $this->performTestsWhenUserSelectedUnavailableTime(
            'get',
            route('rooms.price', $room),
            $room,
        );
    }

    /** @test */
    public function it_calculates_price_for_rehearsal_even_when_time_is_booked_by_this_rehearsal(): void
    {
        $organization = $this->createOrganization();
        $room = $this->createOrganizationRoom($organization);

        $this->createPricesForOrganization($organization);

        $user = $this->createUser();

        $this->actingAs($user);

        $rehearsal = $this->createRehearsal(10, 12, $organization, null, false, $user);

        $this->assertEquals(1, Rehearsal::count());

        $newRehearsalStartTime = $rehearsal->time->from()->subHour();
        $newRehearsalEndTime = $rehearsal->time->to()->addHour();

        $response = $this->json(
            'get',
            route('rooms.price', $room),
            [
                'starts_at' => $newRehearsalStartTime->toDateTimeString(),
                'ends_at' => $newRehearsalEndTime->toDateTimeString(),
                'rehearsal_id' => $rehearsal->id
            ]
        );

        $response->assertOk();
    }
}

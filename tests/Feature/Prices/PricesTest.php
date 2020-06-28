<?php

namespace Tests\Feature\Prices;

use App\Models\Organization\Organization;
use Belamov\PostgresRange\Ranges\TimeRange;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Rehearsals\Booking\ValidatesRehearsalTime;
use Tests\TestCase;

/**
 * Class RehearsalsFilterTest.
 */
class PricesTest extends TestCase
{
    use RefreshDatabase;
    use ValidatesRehearsalTime;

    private Organization $organization;

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
            route('organizations.price', $this->organization),
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
    public function it_responses_with_404_when_unknown_organization_is_given(): void
    {
        $this->get(route('organizations.price', 1000),
            [
                'starts_at' => $this->getDateTimeAtMonday(10, 00),
                'ends_at' => $this->getDateTimeAtMonday(11, 30),
            ])->assertNotFound();
        $this->get(route('organizations.price', 'unknown'),
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
        $organization = $this->createOrganization();

        $response = $this->json(
            'get',
            route('organizations.price', $organization),
            $data
        );

        $response->assertJsonValidationErrors($keyWithError);
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_provided_time_when_organization_is_closed(): void
    {
        $organization = $this->createOrganization();

        $this->performTestWhenOrganizationIsClosed(
            'get',
            route('organizations.price', $organization),
            $organization,
        );
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_provided_incorrect_rehearsal_duration(): void
    {
        $organization = $this->createOrganization();

        $this->performTestsWhenUserProvidedIncorrectRehearsalDuration(
            'get',
            route('organizations.price', $organization),
            $organization
        );
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_tries_to_book_rehearsal_longer_than_24_hours(): void
    {
        $organization = $this->createOrganization();

        $this->performTestsWhenUserProvidesRehearsalTimeLongerThan24Hours(
            'get',
            route('organizations.price', $organization),
            $organization,
        );
    }

    /** @test */
    public function it_responds_with_validation_error_when_user_selected_unavailable_time(): void
    {
        $organization = $this->createOrganization();

        $this->performTestsWhenUserSelectedUnavailableTime(
            'get',
            route('organizations.price', $organization),
            $organization,
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = $this->createOrganization();

        // prices at monday
        // 10-14 100
        // 14-20 200
        // 20-00 300
        $this->organization->prices()->createMany([
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
}

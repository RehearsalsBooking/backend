<?php

namespace Tests\Feature\Management\Organizations;

use App\Models\Organization\OrganizationRoom;
use Carbon\CarbonImmutable;
use Database\Seeders\RehearsalsForStatisticsSeeder;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class OrganizationStatisticsTest extends ManagementTestCase
{
    public const YEARS = 3;
    public const MONTHS = 5;
    public const DAYS = 2;
    public const PER_DAY = 2;
    public const PRICE = 100;
    protected CarbonImmutable $startingDate;
    private string $totalEndpoint = 'management.organizations.statistics.total';
    private string $groupedEndpoint = 'management.organizations.statistics.grouped';
    private string $httpVerb = 'get';
    private OrganizationRoom $blueRoom;
    private OrganizationRoom $redRoom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->blueRoom = $this->organizationRoom;
        $this->redRoom = $this->createOrganizationRoom($this->organization);
        $this->startingDate = CarbonImmutable::create(2020, 1, 1, 10);
    }

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        foreach ([$this->totalEndpoint, $this->groupedEndpoint] as $endpoint) {
            $this->json($this->httpVerb, route($endpoint, 1))
                ->assertStatus(Response::HTTP_UNAUTHORIZED);
        }
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoints_is_accessed_not_by_organization_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();

        $data = ['interval' => 'day'];

        foreach ([$this->totalEndpoint, $this->groupedEndpoint] as $endpoint) {
            $this->actingAs($ordinaryClient);
            $this->json($this->httpVerb, route($endpoint, $this->organization->id), $data)
                ->assertStatus(Response::HTTP_FORBIDDEN);

            $this->actingAs($managerOfAnotherOrganization);
            $this->json($this->httpVerb, route($endpoint, $this->organization->id), $data)
                ->assertStatus(Response::HTTP_FORBIDDEN);
        }
    }

    /** @test */
    public function it_responds_with_404_when_unknown_organization_is_given(): void
    {
        $this->actingAs($this->manager);
        foreach ([$this->totalEndpoint, $this->groupedEndpoint] as $endpoint) {
            $this->json($this->httpVerb, route($endpoint, 1000))
                ->assertStatus(Response::HTTP_NOT_FOUND);
            $this->json($this->httpVerb, route($endpoint, 'some text'))
                ->assertStatus(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @test
     * @dataProvider invalidDataForGroupedStatisticsRequest
     * @param  array  $data
     */
    public function it_responds_with_unprocessable_error_when_user_provided_invalid_data(
        array $data,
        string $incorrectField
    ): void {
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            $data
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($incorrectField);
    }

    /**
     * @return array
     */
    public function invalidDataForGroupedStatisticsRequest(): array
    {
        return [
            [
                [
                    'interval' => null,
                ],
                'interval'
            ],
            [
                [
                    'interval' => 1,
                ],
                'interval'
            ],
            [
                [
                    'interval' => 'by year',
                ],
                'interval'
            ],
            [
                [
                    'room_id' => 'text',
                ],
                'room_id'
            ],
            [
                [
                    'room_id' => 10000,
                ],
                'room_id'
            ],
        ];
    }

    /** @test */
    public function it_returns_correct_statistics(): void
    {
        $this->actingAs($this->manager);

        $this->seedDatabaseWithStatisticsData($this->redRoom);
        $this->seedDatabaseWithStatisticsData($this->blueRoom);

        $this->testTotalStatistics();
        $this->testTotalStatisticsGroupedByInterval();
        $this->testGroupedByDayStatistics();
        $this->testGroupedByMonthStatistics();
        $this->testGroupedByYearStatistics();
        $this->testGroupedByYearStatisticsRestrictedByInterval();
        $this->testFilteredByRoomStatistics();
        $this->testUnMatchedRoomStatistics();
    }


    protected function testTotalStatistics(): void
    {
        $response = $this->json($this->httpVerb, route($this->totalEndpoint, $this->organization->id));
        $response->assertOk();

        $data = $response->json()[0];

        $expectedCountOfRehearsals = self::YEARS * self::MONTHS * self::DAYS * self::PER_DAY;
        $expectedCountOfRehearsals *= 2; // for each room
        $this->assertEquals($expectedCountOfRehearsals, $data['count']);
        $this->assertEquals($expectedCountOfRehearsals * self::PRICE, $data['income']);
    }

    protected function testTotalStatisticsGroupedByInterval(): void
    {
        $response = $this->json(
            $this->httpVerb,
            route($this->totalEndpoint, $this->organization->id),
            ['from' => $this->startingDate, 'to' => $this->startingDate->clone()->addMonths(2)]
        );
        $response->assertOk();

        $data = $response->json()[0];

        $expectedCountOfRehearsals = 2 * self::DAYS * self::PER_DAY;
        $expectedCountOfRehearsals *= 2; // for each room

        $this->assertEquals($expectedCountOfRehearsals, $data['count']);
        $this->assertEquals($expectedCountOfRehearsals * self::PRICE, $data['income']);

        $response = $this->json(
            $this->httpVerb,
            route($this->totalEndpoint, $this->organization->id),
            ['from' => $this->startingDate->clone()->addMonths(2)]
        );
        $response->assertOk();
        $data = $response->json()[0];

        $expectedCountOfRehearsals = (self::YEARS * self::MONTHS * self::DAYS * self::PER_DAY) - 2 * self::DAYS * self::PER_DAY;
        $expectedCountOfRehearsals *= 2; // for each room

        $this->assertEquals($expectedCountOfRehearsals, $data['count']);
        $this->assertEquals($expectedCountOfRehearsals * self::PRICE, $data['income']);

        $response = $this->json(
            $this->httpVerb,
            route($this->totalEndpoint, $this->organization->id),
            ['to' => $this->startingDate->clone()->addMonths(5)]
        );
        $response->assertOk();
        $data = $response->json()[0];

        $expectedCountOfRehearsals = 5 * self::DAYS * self::PER_DAY;
        $expectedCountOfRehearsals *= 2; // for each room

        $this->assertEquals($expectedCountOfRehearsals, $data['count']);
        $this->assertEquals($expectedCountOfRehearsals * self::PRICE, $data['income']);
    }

    protected function testGroupedByDayStatistics(): void
    {
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            ['interval' => 'day']
        );
        $response->assertOk();

        $data = $response->json();

        $expectedCount = self::PER_DAY;
        $expectedCount *= 2;

        $expectedIncome = self::PER_DAY * self::PRICE;
        $expectedIncome *= 2;

        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedIncome, $dayStatistics['income']);
        }
    }

    protected function testGroupedByMonthStatistics(): void
    {
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            ['interval' => 'month']
        );
        $response->assertOk();

        $data = $response->json();
        $expectedCount = self::PER_DAY * self::DAYS;
        $expectedCount *= 2; // for each room

        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedCount * self::PRICE, $dayStatistics['income']);
        }
    }

    protected function testGroupedByYearStatistics(): void
    {
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            ['interval' => 'year']
        );
        $response->assertOk();

        $data = $response->json();

        $expectedCount = self::PER_DAY * self::DAYS * self::MONTHS;
        $expectedCount *= 2;

        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedCount * self::PRICE, $dayStatistics['income']);
        }
    }

    protected function testGroupedByYearStatisticsRestrictedByInterval(): void
    {
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            [
                'interval' => 'year',
                'from' => $this->startingDate,
                'to' => $this->startingDate->clone()->addYear(),
            ]
        );
        $response->assertOk();

        $data = $response->json();
        $this->assertCount(1, $data);

        $expectedCount = self::PER_DAY * self::DAYS * self::MONTHS;
        $expectedCount *= 2;

        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedCount * self::PRICE, $dayStatistics['income']);
        }
    }

    protected function testFilteredByRoomStatistics(): void
    {
        //total
        $response = $this->json(
            $this->httpVerb,
            route(
                $this->totalEndpoint,
                $this->organization->id
            ),
            ['room_id' => $this->redRoom->id]
        );
        $response->assertOk();

        $data = $response->json()[0];

        $expectedCountOfRehearsals = self::YEARS * self::MONTHS * self::DAYS * self::PER_DAY;

        $this->assertEquals($expectedCountOfRehearsals, $data['count']);
        $this->assertEquals($expectedCountOfRehearsals * self::PRICE, $data['income']);

        //grouped
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            ['interval' => 'day', 'room_id' => $this->blueRoom->id]
        );
        $response->assertOk();

        $data = $response->json();

        $expectedCount = self::PER_DAY;

        $expectedIncome = self::PER_DAY * self::PRICE;

        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedIncome, $dayStatistics['income']);
        }
    }

    protected function seedDatabaseWithStatisticsData(OrganizationRoom $room): void
    {
        $seeder = new RehearsalsForStatisticsSeeder();
        $seeder->run(
            $room,
            $this->startingDate,
            self::YEARS,
            self::MONTHS,
            self::DAYS,
            self::PRICE,
            self::PER_DAY
        );
    }

    private function testUnMatchedRoomStatistics(): void
    {
        $otherManager = $this->createUser();
        $otherOrganization = $this->createOrganizationForUser($otherManager);
        $otherRoom = $this->createOrganizationRoom($otherOrganization);

        $this->seedDatabaseWithStatisticsData($otherRoom);

        $this->actingAs($otherManager);

        //total
        $response = $this->json(
            $this->httpVerb,
            route(
                $this->totalEndpoint,
                $otherOrganization
            ),
            ['room_id' => $this->organizationRoom->id]
        );
        $response->assertOk();
        $this->assertEmpty($response->json());

        //grouped
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $otherOrganization),
            ['interval' => 'day', 'room_id' => $this->blueRoom->id]
        );
        $response->assertOk();
        $this->assertEmpty($response->json());
    }
}

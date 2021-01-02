<?php

namespace Tests\Feature\Management\Organizations;

use Carbon\CarbonImmutable;
use Database\Seeders\RehearsalsForStatisticsSeeder;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class OrganizationStatisticsTest extends ManagementTestCase
{
    /**
     * @var int
     */
    public const YEARS = 3;
    /**
     * @var int
     */
    public const MONTHS = 5;
    /**
     * @var int
     */
    public const DAYS = 2;
    /**
     * @var int
     */
    public const PER_DAY = 2;
    /**
     * @var int
     */
    public const PRICE = 100;
    protected CarbonImmutable $startingDate;
    private string $totalEndpoint = 'management.organizations.statistics.total';
    private string $groupedEndpoint = 'management.organizations.statistics.grouped';
    private string $httpVerb = 'get';

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
    public function it_responds_with_unprocessable_error_when_user_provided_invalid_data(array $data): void
    {
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            $data
        );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('interval');
    }

    /**
     * @return array
     */
    public function invalidDataForGroupedStatisticsRequest(): array
    {
        return [
            [
                [
                ],
            ],
            [
                [
                    'interval' => null,
                ],
            ],
            [
                [
                    'interval' => 1,
                ],
            ],
            [
                [
                    'interval' => 'by year',
                ],
            ],
        ];
    }

    /** @test */
    public function it_returns_correct_total_statistics(): void
    {
        $this->actingAs($this->manager);
        $response = $this->json($this->httpVerb, route($this->totalEndpoint, $this->organization->id));
        $response->assertOk();

        $data = $response->json()[0];

        $expectedCountOfRehearsals = self::YEARS * self::MONTHS * self::DAYS * self::PER_DAY;
        $this->assertEquals($expectedCountOfRehearsals, $data['count']);
        $this->assertEquals($expectedCountOfRehearsals * self::PRICE, $data['income']);
    }

    /** @test */
    public function it_returns_correct_total_statistics_restricted_by_interval(): void
    {
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->totalEndpoint, $this->organization->id),
            ['from' => $this->startingDate, 'to' => $this->startingDate->clone()->addMonths(2)]
        );
        $response->assertOk();

        $data = $response->json()[0];

        $expectedCountOfRehearsals = 2 * self::DAYS * self::PER_DAY;
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
        $this->assertEquals($expectedCountOfRehearsals, $data['count']);
        $this->assertEquals($expectedCountOfRehearsals * self::PRICE, $data['income']);
    }

    /** @test */
    public function it_returns_correct_grouped_by_day_statistics(): void
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            ['interval' => 'day']
        );
        $response->assertOk();

        $data = $response->json();
        foreach ($data as $dayStatistics) {
            $this->assertEquals(self::PER_DAY, $dayStatistics['count']);
            $this->assertEquals(self::PER_DAY * self::PRICE, $dayStatistics['income']);
        }
    }

    /** @test */
    public function it_returns_correct_grouped_by_month_statistics(): void
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            ['interval' => 'month']
        );
        $response->assertOk();

        $data = $response->json();
        $expectedCount = self::PER_DAY * self::DAYS;
        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedCount * self::PRICE, $dayStatistics['income']);
        }
    }

    /** @test */
    public function it_returns_correct_grouped_by_year_statistics(): void
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->manager);
        $response = $this->json(
            $this->httpVerb,
            route($this->groupedEndpoint, $this->organization->id),
            ['interval' => 'year']
        );
        $response->assertOk();

        $data = $response->json();
        $expectedCount = self::PER_DAY * self::DAYS * self::MONTHS;
        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedCount * self::PRICE, $dayStatistics['income']);
        }
    }

    /** @test */
    public function it_returns_correct_grouped_by_year_statistics_restricted_by_interval(): void
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->manager);
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
        foreach ($data as $dayStatistics) {
            $this->assertEquals($expectedCount, $dayStatistics['count']);
            $this->assertEquals($expectedCount * self::PRICE, $dayStatistics['income']);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->startingDate = CarbonImmutable::create(2020, 1, 1, 10);
        (new RehearsalsForStatisticsSeeder())->run(
            $this->organization,
            $this->startingDate,
            self::YEARS,
            self::MONTHS,
            self::DAYS,
            self::PRICE,
            self::PER_DAY
        );
    }
}

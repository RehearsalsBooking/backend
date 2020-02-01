<?php

namespace Tests\Unit\Rehearsals;

use App\Exceptions\User\PriceCalculationException;
use App\Models\Organization;
use App\Models\RehearsalPrice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RehearsalPriceCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Organization
     */
    private Organization $organization;

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
                'starts_at' => '10:00',
                'ends_at' => '14:00',
            ],
            [
                'day' => 1,
                'price' => 200,
                'starts_at' => '14:00',
                'ends_at' => '20:00',
            ],
            [
                'day' => 1,
                'price' => 300,
                'starts_at' => '20:00',
                'ends_at' => '24:00',
            ],
        ]);

        //prices at tuesday
        // 00-06 300
        $this->organization->prices()->createMany([
            [
                'day' => 2,
                'price' => 300,
                'starts_at' => '00:00',
                'ends_at' => '06:00',
            ],
        ]);

        //prices at saturday
        // 10-14 200
        // 14-20 400
        // 20-00 600
        $this->organization->prices()->createMany([
            [
                'day' => 6,
                'price' => 200,
                'starts_at' => '10:00',
                'ends_at' => '14:00',
            ],
            [
                'day' => 6,
                'price' => 400,
                'starts_at' => '14:00',
                'ends_at' => '20:00',
            ],
            [
                'day' => 6,
                'price' => 600,
                'starts_at' => '20:00',
                'ends_at' => '00:00',
            ],
        ]);
    }


    /** @test
     * @throws PriceCalculationException
     */
    public function it_calculates_rehearsal_cost_at_one_period_correctly(): void
    {
        // prices at monday
        // 10-14 100
        // 14-20 200
        // 20-00 300

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(10, 0),
            $this->getDateTimeAtMonday(11, 0),
        );
        $this->assertEquals(100.0, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(10, 0),
            $this->getDateTimeAtMonday(13, 0),
        );
        $this->assertEquals(300.0, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(14, 0),
            $this->getDateTimeAtMonday(14, 30),
        );
        $this->assertEquals(100.0, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(14, 0),
            $this->getDateTimeAtMonday(15, 30),
        );
        $this->assertEquals(300.0, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(20, 0),
            $this->getDateTimeAtMonday(20, 30),
        );
        $this->assertEquals(150.0, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(20, 0),
            $this->getDateTimeAtMonday(22, 0),
        );
        $this->assertEquals(600.0, $price());
    }

    /** @test
     * @throws PriceCalculationException
     */
    public function it_calculates_rehearsal_cost_at_multiple_periods_correctly(): void
    {
        // prices at monday
        // 10-14 100
        // 14-20 200
        // 20-00 300

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(13, 00),
            $this->getDateTimeAtMonday(15, 00),
        );
        $this->assertEquals(100 + 200, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(19, 30),
            $this->getDateTimeAtMonday(21, 30),
        );
        $this->assertEquals(0.5 * 200 + 1.5 * 300, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(13, 00),
            $this->getDateTimeAtMonday(21, 00),
        );
        $this->assertEquals(100 + 6 * 200 + 300, $price());
    }

    /** @test
     * @throws PriceCalculationException
     */
    public function it_calculates_rehearsal_cost_at_multiple_periods_and_days_correctly(): void
    {
        // prices at monday
        // 10-14 100
        // 14-20 200
        // 20-00 300
        //prices at tuesday
        // 00-06 300

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(23, 00),
            $this->getDateTimeAtTuesday(1, 00),
        );
        $this->assertEquals(300 + 300, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(16, 00),
            $this->getDateTimeAtTuesday(1, 00),
        );
        $this->assertEquals(4 * 200 + 4 * 300 + 300, $price());

        $price = new RehearsalPrice(
            $this->organization->id,
            $this->getDateTimeAtMonday(12, 00),
            $this->getDateTimeAtTuesday(1, 00),
        );
        $this->assertEquals(2 * 100 + 6 * 200 + 4 * 300 + 300, $price());
    }

    /**
     * @param int $hour
     * @param int $minute
     * @return Carbon
     */
    private function getDateTimeAtTuesday(int $hour, int $minute): Carbon
    {
        return Carbon::create(2020, 1, 28, $hour, $minute);
    }

    /**
     * @param int $hour
     * @param int $minute
     * @return Carbon
     */
    private function getDateTimeAtMonday(int $hour, int $minute): Carbon
    {
        return Carbon::create(2020, 1, 27, $hour, $minute);
    }
}

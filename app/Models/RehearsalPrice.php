<?php

namespace App\Models;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\PriceCalculationException;
use App\Models\Organization\OrganizationPrice;
use Belamov\PostgresRange\Ranges\TimeRange;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RehearsalPrice.
 * @property int $organizationId
 * @property Carbon $start
 * @property Carbon $end
 * @property int $uncalculatedMinutes
 */
class RehearsalPrice
{
    /**
     * @var int
     */
    private int $organizationId;
    /**
     * @var Carbon
     */
    private Carbon $start;
    /**
     * @var Carbon
     */
    private Carbon $end;
    /**
     * @var int
     */
    private int $uncalculatedMinutes;

    private const MINUTES_IN_ONE_DAY = 60 * 24;
    private const MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES = 30;

    /**
     * RehearsalPrice constructor.
     * @param int $organizationId
     * @param Carbon $start
     * @param Carbon $end
     * @throws InvalidRehearsalDurationException
     */
    public function __construct(int $organizationId, Carbon $start, Carbon $end)
    {
        $this->organizationId = $organizationId;
        $this->start = $start;
        $this->end = $end;
        $this->uncalculatedMinutes = $end->diffInMinutes($start);

        if ($this->uncalculatedMinutes >= self::MINUTES_IN_ONE_DAY) {
            throw new InvalidRehearsalDurationException('Длительность репетиции не может превышать 24 часа');
        }

        if ($this->uncalculatedMinutes % self::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES !== 0) {
            throw new InvalidRehearsalDurationException('Некорректная длительность репетиции');
        }
    }

    /**
     * Calculates price of rehearsal.
     *
     * @return float|int
     * @throws PriceCalculationException
     */
    public function __invoke()
    {
        $dayOfWeekStart = $this->start->dayOfWeek;
        $dayOfWeekEnd = $this->end->dayOfWeek;

        if ($dayOfWeekEnd === $dayOfWeekStart) {
            $result = $this->calculatePriceForSingleDay($dayOfWeekStart, $this->start, $this->end);

            if ($this->uncalculatedMinutes !== 0) {
                throw new PriceCalculationException();
            }

            return $result;
        }

        $priceAtFirstDay = $this->calculatePriceForSingleDay(
            $dayOfWeekStart,
            $this->start,
            $this->start->copy()->hours(24)->minute(0)
        );
        $priceAtLastDay = $this->calculatePriceForSingleDay(
            $dayOfWeekEnd,
            $this->end->copy()->hours(0)->minute(0),
            $this->end
        );

        if ($this->uncalculatedMinutes !== 0) {
            throw new PriceCalculationException();
        }

        return $priceAtFirstDay + $priceAtLastDay;
    }

    /**
     * @param int $day
     * @param Carbon $start
     * @param Carbon $end
     * @return float|int
     * @throws PriceCalculationException
     */
    private function calculatePriceForSingleDay(int $day, Carbon $start, Carbon $end)
    {
        $matchingPrices = $this->getMatchingPricesForPeriod(
            $day,
            $start->toTimeString(),
            $this->transformMidnight($end->toTimeString())
        );

        if ($matchingPrices->count() === 1) {
            return $this->calculatePriceForPeriod(
                $start->toTimeString(),
                $this->transformMidnight($end->toTimeString()),
                $matchingPrices->first()
            );
        }

        $result = 0;

        foreach ($matchingPrices as $index => $price) {
            if ($index === 0) {
                $result += $this->calculatePriceForPeriod(
                    $start->toTimeString(),
                    $price->time->to(),
                    $price
                );
            } elseif ($index === $matchingPrices->count() - 1) {
                $result += $this->calculatePriceForPeriod(
                    $price->time->from(),
                    $this->transformMidnight($end->toTimeString()),
                    $price
                );
            } else {
                $result += $this->calculatePriceForPeriod(
                    $price->time->from(),
                    $price->time->to(),
                    $price
                );
            }
        }

        return $result;
    }

    /**
     * @param int $day
     * @param $timeStart
     * @param $timeEnd
     * @return OrganizationPrice[]|Collection
     */
    private function getMatchingPricesForPeriod(int $day, $timeStart, $timeEnd): Collection
    {
        return OrganizationPrice::where('organization_id', $this->organizationId)
            ->where('day', $day)
            ->whereRaw('time && ?::timerange', [new TimeRange($timeStart, $timeEnd)])
            ->orderBy('time')
            ->get();
    }

    /**
     * Transforms midnight time to 24:00 for correct queries.
     *
     * @param string $time
     * @return string
     */
    private function transformMidnight(string $time): string
    {
        return $time === '00:00:00' ? '24:00:00' : $time;
    }

    /**
     * @param string $timeStart
     * @param string $timeEnd
     * @param OrganizationPrice $price cost of one hour of rehearsal
     * @return float|int
     * @throws PriceCalculationException
     */
    private function calculatePriceForPeriod(string $timeStart, string $timeEnd, OrganizationPrice $price)
    {
        if ($timeStart < $price->time->from() || $timeEnd > $this->transformMidnight($price->time->to())) {
            throw new PriceCalculationException();
        }
        $periodStart = Carbon::createFromTimeString($timeStart);
        $periodEnd = Carbon::createFromTimeString($timeEnd);
        $delta = $periodEnd->diffInMinutes($periodStart);
        $this->uncalculatedMinutes -= $delta;

        return $delta * $price->price / 60;
    }
}

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
 *
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
     *
     * @param  int  $organizationId
     * @param  Carbon  $start
     * @param  Carbon  $end
     * @throws InvalidRehearsalDurationException
     */
    public function __construct(int $organizationId, Carbon $start, Carbon $end)
    {
        $this->organizationId = $organizationId;
        $this->start = $start;
        $this->end = $end;
        $this->uncalculatedMinutes = $end->diffInMinutes($start);

        if ($this->isEndOfTheDay($end)) {
            $this->uncalculatedMinutes++;
        }

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
    public function __invoke(): float
    {
        $price = $this->isRehearsalDuringOneDay() ?
            $this->calculatePriceForSingleDay($this->start->dayOfWeek, $this->start, $this->end) :
            $this->calculatePriceForTwoDays();

        if ($this->uncalculatedMinutes !== 0) {
            throw new PriceCalculationException();
        }

        return $price;
    }

    /**
     * @param  int  $day
     * @param  Carbon  $start
     * @param  Carbon  $end
     * @return float
     * @throws PriceCalculationException
     */
    private function calculatePriceForSingleDay(int $day, Carbon $start, Carbon $end): float
    {
        $matchingPrices = $this->getMatchingPricesForPeriod($day, $start, $end);

        return $matchingPrices->reduce(
            fn(
                float $result,
                OrganizationPrice $price
            ) => $result + $this->calculatePriceForPeriod($price->time->from(), $price->time->to(), $price->price),
            0);
    }

    /**
     * @param  int  $day
     * @param  Carbon  $timeStart
     * @param  Carbon  $timeEnd
     * @return OrganizationPrice[]|Collection
     * @throws PriceCalculationException
     */
    private function getMatchingPricesForPeriod(int $day, Carbon $timeStart, Carbon $timeEnd): Collection
    {
        $matchingPrices = OrganizationPrice::where('organization_id', $this->organizationId)
            ->where('day', $day)
            ->whereRaw('time && ?::timerange', [new TimeRange($timeStart, $timeEnd)])
            ->orderBy('time')
            ->get();

        return $this->setPricesBoundaries($matchingPrices, $timeStart, $timeEnd);
    }

    /**
     * @param  string  $from
     * @param  string  $to
     * @param  int  $price  cost of one hour of rehearsal
     * @return float
     */
    private function calculatePriceForPeriod(string $from, string $to, int $price): float
    {
        $periodStart = Carbon::createFromTimeString($from);
        $periodEnd = Carbon::createFromTimeString($to);

        $delta = $periodEnd->diffInMinutes($periodStart);
        $delta = $this->isEndOfTheDay($periodEnd) ? $delta + 1 : $delta;

        $this->uncalculatedMinutes -= $delta;

        return $delta * $price / 60;
    }

    /**
     * @param  Carbon  $time
     * @return bool
     */
    private function isEndOfTheDay(Carbon $time): bool
    {
        return $time->hour === 23 && $time->minute === 59;
    }

    /**
     * @return bool
     */
    private function isRehearsalDuringOneDay(): bool
    {
        return $this->start->dayOfWeek === $this->end->dayOfWeek;
    }

    /**
     * @param  Collection  $matchingPrices
     * @param  Carbon  $timeStart
     * @param  Carbon  $timeEnd
     * @return Collection
     * @throws PriceCalculationException
     */
    private function setPricesBoundaries(Collection $matchingPrices, Carbon $timeStart, Carbon $timeEnd): Collection
    {
        if ($matchingPrices->isEmpty()) {
            return $matchingPrices;
        }

        $this->checkBoundaries($matchingPrices, $timeStart, $timeEnd);

        $matchingPrices->first()->time = new TimeRange($timeStart, $matchingPrices->first()->time->to());
        $matchingPrices->last()->time = new TimeRange($matchingPrices->last()->time->from(), $timeEnd);

        return $matchingPrices;
    }

    /**
     * @return float
     * @throws PriceCalculationException
     */
    private function calculatePriceForTwoDays(): float
    {
        $priceAtFirstDay = $this->calculatePriceForSingleDay(
            $this->start->dayOfWeek,
            $this->start,
            $this->start->copy()->hours(23)->minute(59)
        );

        $priceAtLastDay = $this->calculatePriceForSingleDay(
            $this->end->dayOfWeek,
            $this->end->copy()->hours(0)->minute(0),
            $this->end
        );

        return $priceAtFirstDay + $priceAtLastDay;
    }

    /**
     * @param  Carbon  $timeStart
     * @param  Collection  $matchingPrices
     * @param  Carbon  $timeEnd
     * @throws PriceCalculationException
     */
    private function checkBoundaries(Collection $matchingPrices, Carbon $timeStart, Carbon $timeEnd): void
    {
        if ($timeStart->toTimeString() < $matchingPrices->first()->time->from() ||
            $timeEnd->toTimeString() > $matchingPrices->last()->time->to()
        ) {
            throw new PriceCalculationException();
        }
    }
}

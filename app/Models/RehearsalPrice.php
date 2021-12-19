<?php

namespace App\Models;

use App\Exceptions\User\PriceCalculationException;
use App\Models\Organization\OrganizationRoomPrice;
use Belamov\PostgresRange\Ranges\TimeRange;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

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
    private int $uncalculatedMinutes;

    public function __construct(
        private int $organizationRoomId,
        private CarbonImmutable $start,
        private CarbonImmutable $end
    ) {
        $this->uncalculatedMinutes = $end->diffInMinutes($start);

        if ($this->isEndOfTheDay($end)) {
            $this->uncalculatedMinutes++;
        }
    }

    /**
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
     * @throws PriceCalculationException
     */
    private function calculatePriceForSingleDay(int $day, CarbonImmutable $start, CarbonImmutable $end): float
    {
        return $this->getMatchingPricesForPeriod($day, $start, $end)->reduce(
            function (float $result, OrganizationRoomPrice $price) {
                return $result + $this->calculatePriceForPeriod(
                        $price->time->from() ?? '',
                        $price->time->to() ?? '',
                        $price->price
                    );
            },
            0
        );
    }

    /**
     * @throws PriceCalculationException
     */
    private function getMatchingPricesForPeriod(
        int $day,
        CarbonImmutable $timeStart,
        CarbonImmutable $timeEnd
    ): Collection {
        $matchingPrices = OrganizationRoomPrice::where('organization_room_id', $this->organizationRoomId)
            ->where('day', $day)
            ->whereRaw('time && ?::timerange', [new TimeRange($timeStart, $timeEnd)])
            ->orderBy('time')
            ->get();

        return $this->setPricesBoundaries($matchingPrices, $timeStart, $timeEnd);
    }

    private function calculatePriceForPeriod(string $from, string $to, float $price): float
    {
        $periodStart = CarbonImmutable::createFromTimeString($from);
        $periodEnd = CarbonImmutable::createFromTimeString($to);

        $delta = $periodEnd->diffInMinutes($periodStart);
        $delta = $this->isEndOfTheDay($periodEnd) ? $delta + 1 : $delta;

        $this->uncalculatedMinutes -= $delta;

        return $delta * $price / 60;
    }

    private function isEndOfTheDay(CarbonImmutable $time): bool
    {
        return $time->hour === 23 && $time->minute === 59;
    }

    private function isRehearsalDuringOneDay(): bool
    {
        return $this->start->dayOfWeek === $this->end->dayOfWeek;
    }

    /**
     * @throws PriceCalculationException
     */
    private function setPricesBoundaries(
        Collection $matchingPrices,
        CarbonImmutable $timeStart,
        CarbonImmutable $timeEnd
    ): Collection {
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
     * @throws PriceCalculationException
     */
    private function checkBoundaries(
        Collection $matchingPrices,
        CarbonImmutable $timeStart,
        CarbonImmutable $timeEnd
    ): void {
        if ($timeStart->toTimeString() < $matchingPrices->first()->time->from() ||
            $timeEnd->toTimeString() > $matchingPrices->last()->time->to()
        ) {
            throw new PriceCalculationException();
        }
    }
}

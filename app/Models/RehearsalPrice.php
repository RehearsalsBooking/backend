<?php


namespace App\Models;

use App\Exceptions\User\PriceCalculationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RehearsalPrice
 * @package App\Models
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

    public function __construct(int $organizationId, Carbon $start, Carbon $end)
    {
        $this->organizationId = $organizationId;
        $this->start = $start;
        $this->end = $end;
        $this->uncalculatedMinutes = $end->diffInMinutes($start);
    }

    /**
     * Calculates price of rehearsal
     *
     * @return float|int
     * @throws PriceCalculationException
     */
    public function __invoke()
    {
        $dayOfWeekStart = $this->start->dayOfWeekIso;
        $dayOfWeekEnd = $this->end->dayOfWeekIso;

        if ($dayOfWeekEnd === $dayOfWeekStart) {
            $result = $this->calculatePriceForSingleDay($dayOfWeekStart, $this->start, $this->end);

            if ($this->uncalculatedMinutes !== 0) {
                throw new PriceCalculationException();
            }

            return $result;
        }

        // todo: rehearsal cannot be longer than 24 hours
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
        /** @var Price $price */
        foreach ($matchingPrices as $index => $price) {
            if ($index === 0) {
                $result += $this->calculatePriceForPeriod(
                    $start->toTimeString(),
                    $price->ends_at,
                    $price
                );
            } elseif ($index === $matchingPrices->count() - 1) {
                $result += $this->calculatePriceForPeriod(
                    $price->starts_at,
                    $this->transformMidnight($end->toTimeString()),
                    $price
                );
            } else {
                $result += $this->calculatePriceForPeriod(
                    $price->starts_at,
                    $price->ends_at,
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
     * @return Collection
     */
    private function getMatchingPricesForPeriod(int $day, $timeStart, $timeEnd): Collection
    {
        return Price::where('organization_id', $this->organizationId)
            ->where('day', $day)
            ->where(
                fn (Builder $query) => $query
                    ->where(
                        fn (Builder $query) => $query->where('starts_at', '<=', $timeStart)
                            ->where('ends_at', '>', $timeEnd)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '<=', $timeStart)
                            ->where('ends_at', '>', $timeStart)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '<=', $timeEnd)
                            ->where('ends_at', '>', $timeEnd)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '>=', $timeStart)
                            ->where('ends_at', '<=', $timeEnd)
                    )
            )
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Transforms midnight time to 24:00 for correct queries
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
     * @param Price $price cost of one hour of rehearsal
     * @return float|int
     * @throws PriceCalculationException
     */
    private function calculatePriceForPeriod(string $timeStart, string $timeEnd, Price $price)
    {
        if ($timeStart < $price->starts_at || $timeEnd > $price->ends_at) {
            throw new PriceCalculationException();
        }
        $periodStart = Carbon::createFromTimeString($timeStart);
        $periodEnd = Carbon::createFromTimeString($timeEnd);
        $delta = $periodEnd->diffInMinutes($periodStart);
        $this->uncalculatedMinutes -= $delta;
        return ($delta * $price->price / 60);
    }
}

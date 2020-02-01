<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RehearsalPrice
 * @package App\Models
 * @property Organization $organization
 * @property Carbon $start
 * @property Carbon $end
 */
class RehearsalPrice
{
    /**
     * @var Organization
     */
    private Organization $organization;
    /**
     * @var Carbon
     */
    private Carbon $start;
    /**
     * @var Carbon
     */
    private Carbon $end;

    public function __construct(Organization $organization, Carbon $start, Carbon $end)
    {
        $this->organization = $organization;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Calculates price of rehearsal
     *
     * @return float|int
     */
    public function __invoke()
    {
        $dayOfWeekStart = $this->start->dayOfWeekIso;
        $dayOfWeekEnd = $this->end->dayOfWeekIso;

        if ($dayOfWeekEnd === $dayOfWeekStart) {
            return $this->calculatePriceForSingleDay($dayOfWeekStart, $this->start, $this->end);
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
        return $priceAtFirstDay + $priceAtLastDay;
    }

    /**
     * @param int $day
     * @param Carbon $start
     * @param Carbon $end
     * @return float|int
     */
    private function calculatePriceForSingleDay(int $day, Carbon $start, Carbon $end)
    {
        $matchingPrices = $this->getMatchingPricesForPeriod(
            $day,
            $start->toTimeString(),
            $this->transformMidnight($end->toTimeString())
        );

        if ($matchingPrices->count() === 1) {
            return $this->calculatePriceForPeriod($start, $end, $matchingPrices->first()->price);
        }

        $result = 0;
        /** @var Price $price */
        foreach ($matchingPrices as $index => $price) {
            if ($index === 0) {
                $result += $this->calculatePriceForPeriod(
                    $start->toTimeString(),
                    $price->ends_at,
                    $price->price
                );
            } elseif ($index === $matchingPrices->count() - 1) {
                $result += $this->calculatePriceForPeriod(
                    $price->starts_at,
                    $this->transformMidnight($end->toTimeString()),
                    $price->price
                );
            } else {
                $result += $this->calculatePriceForPeriod(
                    $price->starts_at,
                    $price->ends_at,
                    $price->price
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
        return $this->organization->prices()
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
     * @param float $price cost of one hour of rehearsal
     * @return float|int
     */
    private function calculatePriceForPeriod(string $timeStart, string $timeEnd, float $price)
    {
        $periodStart = Carbon::createFromTimeString($timeStart);
        $periodEnd = Carbon::createFromTimeString($timeEnd);
        $delta = $periodEnd->diffInMinutes($periodStart);
        return ($delta * $price / 60);
    }
}

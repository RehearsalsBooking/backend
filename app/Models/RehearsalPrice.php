<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

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


    }

    /**
     * @param int $day
     * @param Carbon $start
     * @param Carbon $end
     * @return float|int
     */
    private function calculatePriceForSingleDay(int $day, Carbon $start, Carbon $end)
    {
        $timeStart = $start->toTimeString();
        $timeEnd = $end->toTimeString();

        $matchingPrices = $this->organization->prices()
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

        // if rehearsal's durations matches only one price setting
        // then simply calculate it's cost according to duration
        if ($matchingPrices->count() === 1) {
            return $this->calculatePriceForPeriod($start, $end, $matchingPrices->first()->price);
        }

        $result = 0;
        foreach ($matchingPrices as $index => $price) {
            if ($index === 0) {
                $result += $this->calculatePriceForPeriod($start->toTimeString(), $price->ends_at, $price->price);
            } elseif ($index === $matchingPrices->count() - 1) {
                $result += $this->calculatePriceForPeriod($price->starts_at, $end->toTimeString(), $price->price);
            } else {
                $result += $this->calculatePriceForPeriod($price->starts_at, $price->ends_at, $price->price);
            }
        }

        return $result;
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

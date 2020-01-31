<?php

namespace App\Models;

use App\Models\GlobalScopes\OnlyActiveScope;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Organization
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $coordinates
 * @property bool $verified
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Organization newModelQuery()
 * @method static Builder|Organization newQuery()
 * @method static Builder|Organization query()
 * @method static Builder|Organization whereAddress($value)
 * @method static Builder|Organization whereCoordinates($value)
 * @method static Builder|Organization whereCreatedAt($value)
 * @method static Builder|Organization whereId($value)
 * @method static Builder|Organization whereName($value)
 * @method static Builder|Organization whereUpdatedAt($value)
 * @method static Builder|Organization whereVerified($value)
 * @mixin Eloquent
 * @method static Builder|Organization verified()
 * @property-read User $owner
 * @property-read Collection|WorkingDay[] $workingDays
 * @property string|null $description
 * @property int $owner_id
 * @property-read Collection|Rehearsal[] $rehearsals
 * @property-read int|null $rehearsals_count
 * @method static Builder|Organization whereDescription($value)
 * @method static Builder|Organization whereOwnerId($value)
 * @property int|null $opens_at
 * @property int|null $closes_at
 * @method static Builder|Organization whereClosesAt($value)
 * @method static Builder|Organization whereOpensAt($value)
 * @property bool $is_active
 * @method static Builder|Organization whereIsActive($value)
 * @property-read Collection|Price[] $prices
 * @property-read int|null $prices_count
 */
class Organization extends Model
{
    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new OnlyActiveScope);
    }

    /**
     * Filters query by only verified organizations
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verified', true);
    }

    /**
     * Owner of organization
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @param $startsAt
     * @param $endsAt
     * @param Rehearsal|null $rehearsal
     * @return bool
     */
    public function isTimeAvailable($startsAt, $endsAt, Rehearsal $rehearsal = null): bool
    {
        $inWorkDayRange = $this->isTimeAfterOrganizationOpens($startsAt) && $this->isTimeBeforeOrganizationCloses($endsAt);

        $query = $this->rehearsals()
            ->where(
                fn (Builder $query) => $query
                    ->where(
                        fn (Builder $query) => $query->where('starts_at', '<', $startsAt)
                            ->where('ends_at', '>', $startsAt)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '<', $endsAt)
                            ->where('ends_at', '>', $endsAt)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '>', $startsAt)
                            ->where('ends_at', '<', $endsAt)
                    )
            );

        // if rehearsal was passed as a parameter, then we want to determine if this rehearsal
        // is available for reschedule, so we must exclude it from query
        if ($rehearsal) {
            $query->where('id', '!=', $rehearsal->id);
        }

        return $inWorkDayRange && $query->doesntExist();
    }

    /**
     * @param $time
     * @return bool
     */
    protected function isTimeAfterOrganizationOpens($time): bool
    {
        return strtotime(optional(Carbon::make($time))->format('H:i')) >= strtotime($this->opens_at);
    }

    /**
     * @param $time
     * @return bool
     */
    protected function isTimeBeforeOrganizationCloses($time): bool
    {
        return strtotime(optional(Carbon::make($time))->format('H:i')) <= strtotime($this->closes_at);
    }

    /**
     * Rehearsals of organization
     *
     * @return HasMany
     */
    public function rehearsals(): HasMany
    {
        return $this->hasMany(Rehearsal::class);
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @return float|int
     * TODO: convert 00-00 time to 24-00 time when admin updates prices
     * TODO: restrict rehearsal time to 24 hours
     */
    public function calculatePriceForRehearsal(Carbon $start, Carbon $end): float
    {
        $dayOfWeekStart = $start->dayOfWeekIso;
        $dayOfWeekEnd = $end->dayOfWeekIso;

        $rehearsalDurationInMinutes = $end->diffInMinutes($start);

        if ($dayOfWeekEnd === $dayOfWeekStart) {
            return $this->calculatePriceForSingleDay($dayOfWeekStart, $start, $end, $rehearsalDurationInMinutes);
        }
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    private function calculatePriceForSingleDay(int $day, Carbon $start, Carbon $end, int $rehearsalDurationInMinutes)
    {
        $timeStart = $start->toTimeString();
        $timeEnd = $end->toTimeString();

        $matchingPrices = $this->prices()
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
            return $rehearsalDurationInMinutes * $matchingPrices->first()->price / 60;
        }

        $result = 0;
        foreach ($matchingPrices as $index => $price) {
            if ($index === 0) {
                $timeStart = Carbon::createFromTimeString($start->toTimeString());
                $priceEnd = Carbon::createFromTimeString($price->ends_at);
                $delta = $priceEnd->diffInMinutes($timeStart);
                $result += ($delta * $price->price / 60);
            } elseif ($index === $matchingPrices->count() - 1) {
                $timeStart = Carbon::createFromTimeString($end->toTimeString());
                $priceEnd = Carbon::createFromTimeString($price->starts_at);
                $delta = $priceEnd->diffInMinutes($timeStart);
                $result += ($delta * $price->price / 60);
            } else {
                $timeStart = Carbon::createFromTimeString($price->starts_at);
                $priceEnd = Carbon::createFromTimeString($price->ends_at);
                $delta = $priceEnd->diffInMinutes($timeStart);
                $result += ($delta * $price->price / 60);
            }
        }


        return $result;
    }
}

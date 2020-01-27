<?php

namespace App\Models;

use App\Models\GlobalScopes\OnlyActiveScope;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
     * Rehearsals of organization
     *
     * @return HasMany
     */
    public function rehearsals(): HasMany
    {
        return $this->hasMany(Rehearsal::class);
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
            ->where(fn (Builder $query) =>

                $query
                    ->where(fn (Builder $query) =>
                        $query->where('starts_at', '<', $startsAt)
                            ->where('ends_at', '>', $startsAt)
                    )
                    ->orWhere(fn (Builder $query) =>
                        $query->where('starts_at', '<', $endsAt)
                            ->where('ends_at', '>', $endsAt)
                    )
                    ->orWhere(fn (Builder $query) =>
                        $query->where('starts_at', '>', $startsAt)
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
}

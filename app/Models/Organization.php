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
 */
class Organization extends Model
{

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
     * @return bool
     */
    public function isTimeAvailable($startsAt, $endsAt): bool
    {
        return $this->rehearsals()
            ->where(static function (Builder $query) use ($startsAt) {
                $query->where('starts_at', '<', $startsAt)
                    ->where('ends_at', '>', $startsAt);
            })
            ->orWhere(static function (Builder $query) use ($endsAt) {
                $query->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $endsAt);
            })
            ->orWhere(static function (Builder $query) use ($endsAt, $startsAt) {
                $query->where('starts_at', '>', $startsAt)
                    ->where('ends_at', '<', $endsAt);
            })
            ->doesntExist();
    }
}

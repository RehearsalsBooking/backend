<?php

namespace App\Models;

use App\Models\GlobalScopes\OnlyActiveScope;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property bool $is_active
 * @method static Builder|Organization whereIsActive($value)
 * @property-read Collection|OrganizationPrice[] $prices
 * @property-read int|null $prices_count
 * @property-read Collection|User[] $bannedUsers
 * @property-read int|null $banned_users_count
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

        return $query->doesntExist();
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

    public function prices(): HasMany
    {
        return $this->hasMany(OrganizationPrice::class);
    }

    public function bannedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organizations_users_bans')
            ->using(OrganizationUserBan::class)
            ->withPivot(['comment'])
            ->withTimestamps();
    }

    /**
     * @param $day
     * @param $startsAt
     * @param $endsAt
     * @return bool
     */
    public function hasPriceAt($day, $startsAt, $endsAt): bool
    {
        return OrganizationPrice::where('organization_id', $this->id)
            ->where('day', $day)
            ->where(
                fn (Builder $query) => $query
                    ->where(
                        fn (Builder $query) => $query->where('starts_at', '<=', $startsAt)
                            ->where('ends_at', '>', $endsAt)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '<=', $startsAt)
                            ->where('ends_at', '>', $startsAt)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '<=', $endsAt)
                            ->where('ends_at', '>', $endsAt)
                    )
                    ->orWhere(
                        fn (Builder $query) => $query->where('starts_at', '>=', $startsAt)
                            ->where('ends_at', '<=', $endsAt)
                    )
            )
            ->exists();
    }

    /**
     * @param OrganizationPrice $price
     * @return bool
     */
    public function hasPrice(OrganizationPrice $price): bool
    {
        return $this->prices->contains($price);
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function isUserBanned(int $userId): bool
    {
        return $this->bannedUsers->contains($userId);
    }
}

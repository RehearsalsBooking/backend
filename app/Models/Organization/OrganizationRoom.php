<?php

namespace App\Models\Organization;

use App\Models\Rehearsal;
use Belamov\PostgresRange\Ranges\TimeRange;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Database\Factories\OrganizationRoomFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organization\OrganizationRoom
 *
 * @property int $id
 * @property string $name
 * @property int $organization_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Organization $organization
 * @method static OrganizationRoomFactory factory(...$parameters)
 * @method static Builder|OrganizationRoom newModelQuery()
 * @method static Builder|OrganizationRoom newQuery()
 * @method static Builder|OrganizationRoom query()
 * @mixin Eloquent
 * @property-read Collection|OrganizationRoomPrice[] $prices
 * @property-read int|null $prices_count
 * @property-read Collection|Rehearsal[] $rehearsals
 * @property-read int|null $rehearsals_count
 * @method static Builder|OrganizationRoom whereCreatedAt($value)
 * @method static Builder|OrganizationRoom whereId($value)
 * @method static Builder|OrganizationRoom whereName($value)
 * @method static Builder|OrganizationRoom whereOrganizationId($value)
 * @method static Builder|OrganizationRoom whereUpdatedAt($value)
 * @property-read Collection|Rehearsal[] $futureRehearsals
 * @property-read int|null $future_rehearsals_count
 */
class OrganizationRoom extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): OrganizationRoomFactory
    {
        return OrganizationRoomFactory::new();
    }

    /**
     * @return BelongsTo<Organization, self>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * @return HasMany<OrganizationRoomPrice>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(OrganizationRoomPrice::class);
    }

    /**
     * @return HasMany<Rehearsal>
     */
    public function rehearsals(): HasMany
    {
        return $this->hasMany(Rehearsal::class);
    }

    /**
     * @return HasMany<Rehearsal>
     */
    public function futureRehearsals(): HasMany
    {
        return $this->rehearsals()->whereRaw('time && ?', [
            new TimestampRange(Carbon::now(), null),
        ]);
    }

    public function hasPriceAt(int $day, string $startsAt, string $endsAt): bool
    {
        return OrganizationRoomPrice::where('organization_room_id', $this->id)
            ->where('day', $day)
            ->whereRaw('time && ?::timerange', [new TimeRange($startsAt, $endsAt)])
            ->exists();
    }
}
<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Ranges\TimeRange;
use Database\Factories\OrganizationRoomFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organization\OrganizationRoom
 *
 * @property int $id
 * @property string|null $name
 * @property int $organization_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static OrganizationRoomFactory factory(...$parameters)
 * @method static Builder|OrganizationRoom newModelQuery()
 * @method static Builder|OrganizationRoom newQuery()
 * @method static Builder|OrganizationRoom query()
 * @method static Builder|OrganizationRoom whereCreatedAt($value)
 * @method static Builder|OrganizationRoom whereId($value)
 * @method static Builder|OrganizationRoom whereName($value)
 * @method static Builder|OrganizationRoom whereOrganizationId($value)
 * @method static Builder|OrganizationRoom whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrganizationRoom extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): OrganizationRoomFactory
    {
        return OrganizationRoomFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(OrganizationRoomPrice::class);
    }

    public function hasPriceAt(int $day, string $startsAt, string $endsAt): bool
    {
        return OrganizationRoomPrice::where('organization_room_id', $this->id)
            ->where('day', $day)
            ->whereRaw('time && ?::timerange', [new TimeRange($startsAt, $endsAt)])
            ->exists();
    }

}
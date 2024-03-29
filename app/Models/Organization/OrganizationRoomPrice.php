<?php

namespace App\Models\Organization;

use Belamov\PostgresRange\Casts\TimeRangeCast;
use Belamov\PostgresRange\Ranges\TimeRange;
use Database\Factories\OrganizationPriceFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Price.
 *
 * @property int $id
 * @property int $day
 * @property float $price
 * @property int $organization_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization $organization
 * @method static Builder|OrganizationRoomPrice newModelQuery()
 * @method static Builder|OrganizationRoomPrice newQuery()
 * @method static Builder|OrganizationRoomPrice query()
 * @method static Builder|OrganizationRoomPrice whereCreatedAt($value)
 * @method static Builder|OrganizationRoomPrice whereDay($value)
 * @method static Builder|OrganizationRoomPrice whereId($value)
 * @method static Builder|OrganizationRoomPrice whereOrganizationId($value)
 * @method static Builder|OrganizationRoomPrice wherePrice($value)
 * @method static Builder|OrganizationRoomPrice whereUpdatedAt($value)
 * @mixin Eloquent
 * @property TimeRange $time
 * @method static Builder|OrganizationRoomPrice whereTime($value)
 * @method static OrganizationPriceFactory factory(...$parameters)
 * @property int $organization_room_id
 * @property-read OrganizationRoom $room
 * @method static Builder|OrganizationRoomPrice whereOrganizationRoomId($value)
 */
class OrganizationRoomPrice extends Model
{
    use HasFactory;

    protected static function newFactory(): OrganizationPriceFactory
    {
        return OrganizationPriceFactory::new();
    }

    protected $guarded = ['id'];

    protected $casts = [
        'time' => TimeRangeCast::class,
    ];

    /**
     * @return BelongsTo<OrganizationRoom, self>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(OrganizationRoom::class, 'organization_room_id');
    }
}

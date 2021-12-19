<?php

namespace App\Models;

use App\Http\Requests\Filters\FilterRequest;
use App\Models\Organization\OrganizationRoom;
use Belamov\PostgresRange\Casts\TimestampRangeCast;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Database\Factories\RehearsalFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Rehearsal.
 *
 * @property int $id
 * @property int $user_id
 * @property bool $is_paid
 * @property float $price
 * @property int|null $band_id
 * @property TimestampRange $time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrganizationRoom $room
 * @property-read User $user
 * @property-read Band|null $band
 * @property-read Collection|User[] $attendees
 * @property-read int|null $attendees_count
 * @method static Builder|Rehearsal newModelQuery()
 * @method static Builder|Rehearsal newQuery()
 * @method static Builder|Rehearsal query()
 * @method static Builder|Rehearsal filter(FilterRequest $filters)
 * @method static Builder|Rehearsal completed()
 * @method static RehearsalFactory factory(...$parameters)
 * @mixin Eloquent
 * @property int $organization_room_id
 * @method static Builder|Rehearsal whereBandId($value)
 * @method static Builder|Rehearsal whereCreatedAt($value)
 * @method static Builder|Rehearsal whereId($value)
 * @method static Builder|Rehearsal whereIsPaid($value)
 * @method static Builder|Rehearsal whereOrganizationRoomId($value)
 * @method static Builder|Rehearsal wherePrice($value)
 * @method static Builder|Rehearsal whereTime($value)
 * @method static Builder|Rehearsal whereUpdatedAt($value)
 * @method static Builder|Rehearsal whereUserId($value)
 */
class Rehearsal extends Model
{
    use Filterable;
    use HasFactory;

    public const MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES = 30;
    public const MAXIMUM_REHEARSAL_DURATION_IN_MINUTES = 60 * 8; // 8 hours

    protected $guarded = ['id'];

    protected $casts = [
        'is_paid' => 'boolean',
        'time' => TimestampRangeCast::class,
    ];

    protected static function booted(): void
    {
        static::created(static function (self $rehearsal) {
            $rehearsal->registerAttendees();
        });
    }

    private function registerAttendees(): void
    {
        $attendees = array_unique(
            array_merge(
                [$this->user->id],
                $this->band?->members->pluck('id')->toArray() ?? []
            )
        );
        $this->attendees()->sync($attendees, true);
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(OrganizationRoom::class, 'organization_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(Band::class);
    }

    public function isIndividual(): bool
    {
        return $this->band_id === null;
    }

    public function isInPast(): bool
    {
        return $this->time->from() < Carbon::now();
    }

    public function scopeCompleted(Builder $builder): Builder
    {
        $tillNow = new TimestampRange(null, now(), '(', ')');

        return $builder->whereRaw("{$tillNow->forSql()} @> time");
    }
}

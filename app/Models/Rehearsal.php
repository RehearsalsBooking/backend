<?php

namespace App\Models;

use App\Http\Requests\Filters\FilterRequest;
use App\Models\Organization\Organization;
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
 * @property int $organization_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization $organization
 * @property-read User $user
 * @method static Builder|Rehearsal newModelQuery()
 * @method static Builder|Rehearsal newQuery()
 * @method static Builder|Rehearsal query()
 * @method static Builder|Rehearsal whereCreatedAt($value)
 * @method static Builder|Rehearsal whereEndsAt($value)
 * @method static Builder|Rehearsal whereId($value)
 * @method static Builder|Rehearsal whereOrganizationId($value)
 * @method static Builder|Rehearsal whereStartsAt($value)
 * @method static Builder|Rehearsal whereUpdatedAt($value)
 * @method static Builder|Rehearsal whereUserId($value)
 * @mixin Eloquent
 * @method static Builder|Rehearsal filter(FilterRequest $filters)
 * @property bool $is_paid
 * @method static Builder|Rehearsal whereIsConfirmed($value)
 * @property int|null $band_id
 * @property-read Band|null $band
 * @method static Builder|Rehearsal whereBandId($value)
 * @property-read Collection|User[] $attendees
 * @property-read int|null $attendees_count
 * @property float $price
 * @method static Builder|Rehearsal wherePrice($value)
 * @property TimestampRange $time
 * @method static Builder|Rehearsal whereTime($value)
 * @method static Builder|Rehearsal completed()
 * @method static RehearsalFactory factory(...$parameters)
 * @method static Builder|Rehearsal whereIsPaid($value)
 */
class Rehearsal extends Model
{
    use Filterable;
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_paid' => 'boolean',
        'time' => TimestampRangeCast::class,
    ];

    protected static function booted()
    {
        static::created(static function (self $rehearsal) {
            $rehearsal->registerAttendees();
        });
    }

    private function registerAttendees(): void
    {
        if ($this->band_id !== null) {
            $this->registerBandMembersAsAttendees();

            return;
        }

        $this->registerUserAsAttendee();
    }

    public function registerBandMembersAsAttendees(): void
    {
        if ($this->band !== null) {
            $bandMembers = $this->band->members;
            $this->attendees()->sync($bandMembers);
        }
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function registerUserAsAttendee(): void
    {
        $this->attendees()->attach($this->user_id);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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

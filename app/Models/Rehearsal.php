<?php

namespace App\Models;

use App\Filters\FilterRequest;
use App\Filters\Filterable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Rehearsal
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
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
 * @property bool $is_confirmed
 * @method static Builder|Rehearsal whereIsConfirmed($value)
 * @property int|null $band_id
 * @property-read Band|null $band
 * @method static Builder|Rehearsal whereBandId($value)
 * @property-read Collection|User[] $attendees
 * @property-read int|null $attendees_count
 * @property float $price
 * @method static Builder|Rehearsal wherePrice($value)
 */
class Rehearsal extends Model
{
    use Filterable;

    protected $guarded = ['id'];

    protected $dates = [
        'starts_at',
        'ends_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean'
    ];

    /**
     * @return BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function band(): BelongsTo
    {
        return $this->belongsTo(Band::class);
    }

    /**
     * @return BelongsToMany
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     *  Adds all of this rehearsals band members as attendees
     */
    public function registerBandMembersAsAttendees(): void
    {
        if ($this->band) {
            $bandMembers = $this->band->members;
            $this->attendees()->sync($bandMembers);
        }
    }

    /**
     *  Adds user who booked this rehearsal as attendee
     */
    public function registerUserAsAttendee(): void
    {
        $this->attendees()->attach($this->user_id);
    }

    /**
     * @return bool
     */
    public function isIndividual(): bool
    {
        return $this->band_id === null;
    }

    /**
     * @return bool
     */
    public function isInPast(): bool
    {
        return $this->starts_at < Carbon::now();
    }
}

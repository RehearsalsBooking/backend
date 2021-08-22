<?php

namespace App\Models\Organization;

use App\Http\Requests\Filters\FilterRequest;
use App\Models\Filterable;
use App\Models\GlobalScopes\OnlyActiveScope;
use App\Models\HasAvatar;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimeRange;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\Carbon;
use Database\Factories\OrganizationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Organization\Organization.
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $coordinates
 * @property string|null $gear
 * @property int $owner_id
 * @property bool $is_active
 * @property string|null $avatar
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
 * @method static Builder|Organization whereGear($value)
 * @method static Builder|Organization whereOwnerId($value)
 * @method static Builder|Organization whereIsActive($value)
 * @method static Builder|Organization whereAvatar($value)
 * @property-read User $owner
 * @property-read Collection|Rehearsal[] $rehearsals
 * @property-read int|null $rehearsals_count
 * @property-read Collection|OrganizationPrice[] $prices
 * @property-read int|null $prices_count
 * @property-read Collection|User[] $bannedUsers
 * @property-read int|null $banned_users_count
 * @method static Builder filter(FilterRequest $filters)
 * @property-read Collection|User[] $favoritedUsers
 * @property-read int|null $favorited_users_count
 * @mixin Eloquent
 * @method static OrganizationFactory factory(...$parameters)
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 */
class Organization extends Model implements HasMedia
{
    use Filterable;
    use HasFactory;
    use HasAvatar;

    protected static function newFactory(): OrganizationFactory
    {
        return OrganizationFactory::new();
    }

    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new OnlyActiveScope());
    }

    public function isUserFavorited(int $userId): bool
    {
        return $this->favoritedUsers()->where('user_id', $userId)->exists();
    }

    public function favoritedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'organizations_users_favorites',
            'organization_id',
            'user_id'
        );
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isTimeAvailable(string $startsAt, string $endsAt, Rehearsal $rehearsal = null): bool
    {
        $query = $this->rehearsals()
            ->whereRaw('time && ?::tsrange', [new TimestampRange($startsAt, $endsAt)]);

        // if rehearsal was passed as a parameter, then we want to determine if this rehearsal
        // is available for reschedule, so we must exclude it from query
        if ($rehearsal !== null) {
            $query->where('id', '!=', $rehearsal->id);
        }

        return $query->doesntExist();
    }

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

    public function hasPriceAt(int $day, string $startsAt, string $endsAt): bool
    {
        return OrganizationPrice::where('organization_id', $this->id)
            ->where('day', $day)
            ->whereRaw('time && ?::timerange', [new TimeRange($startsAt, $endsAt)])
            ->exists();
    }

    public function hasPrice(OrganizationPrice $price): bool
    {
        return $this->prices->contains($price);
    }

    public function isUserBanned(int $userId): bool
    {
        return $this->bannedUsers->contains($userId);
    }

    public function deleteRehearsalsForUserInFuture(int $userId): void
    {
        $this->rehearsals()->whereRaw('time && ?', [new TimestampRange(Carbon::now(), null)])
            ->where('user_id', $userId)
            ->delete();
    }

    public function deleteAvatar(): void
    {
        if ($this->avatar) {
            Storage::disk('public')->delete($this->avatar);
        }
    }
}

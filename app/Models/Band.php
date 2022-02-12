<?php

namespace App\Models;

use App\Http\Requests\Filters\FilterRequest;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Database\Factories\BandFactory;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * App\Models\Band.
 *
 * @property int $id
 * @property string $name
 * @property int $admin_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $admin
 * @method static Builder|Band newModelQuery()
 * @method static Builder|Band newQuery()
 * @method static Builder|Band query()
 * @method static Builder|Band whereAdminId($value)
 * @method static Builder|Band whereCreatedAt($value)
 * @method static Builder|Band whereId($value)
 * @method static Builder|Band whereName($value)
 * @method static Builder|Band whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|User[] $members
 * @property-read int|null $members_count
 * @property-read Collection|Rehearsal[] $rehearsals
 * @property-read int|null $rehearsals_count
 * @property-read Collection|Rehearsal[] $futureRehearsals
 * @property-read int|null $future_rehearsals_count
 * @property Carbon|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Band onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|Band whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Band withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Band withoutTrashed()
 * @property-read Collection|Genre[] $genres
 * @property-read int|null $genres_count
 * @method static Builder|Band filter(FilterRequest $filters)
 * @property string|null $bio
 * @method static Builder|Band whereBio($value)
 * @property-read Collection|Invite[] $invites
 * @property-read int|null $invites_count
 * @method static BandFactory factory(...$parameters)
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read Collection|BandMembership[] $memberships
 * @property-read int|null $memberships_count
 */
class Band extends Model implements HasMedia
{
    use SoftDeletes;
    use HasFactory;
    use Filterable;
    use HasAvatar;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::created(static function (self $band) {
            $band->addMember($band->admin_id);
        });
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /**
     * @return BelongsToMany<Genre>
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'bands_genres',
            'band_id',
            'genre_id'
        );
    }

    public function invite(string $email): Invite
    {
        return Invite::create([
            'email' => $email,
            'band_id' => $this->id,
            'status' => Invite::STATUS_PENDING,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function addMember(int $userId, ?array $roles = null): void
    {
        DB::transaction(function () use ($roles, $userId) {
            $this->memberships()->create(['user_id' => $userId, 'roles' => $roles]);
            $this->addUserToFutureRehearsals($userId);
        });
    }

    /**
     * @return HasManyThrough<User>
     */
    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            BandMembership::class,
            'band_id',
            'users.id',
            'id',
            'user_id',
        );
    }

    /**
     * @return HasMany<BandMembership>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(BandMembership::class);
    }

    private function addUserToFutureRehearsals(int $userId): void
    {
        //TODO: optimize query
        $this->futureRehearsals->each(static function (Rehearsal $futureRehearsal) use ($userId) {
            $futureRehearsal->attendees()->attach($userId);
        });
    }

    /**
     * @throws Throwable
     */
    public function removeMembership(BandMembership $membership): void
    {
        DB::transaction(function () use ($membership) {
            $this->removeUserFromFutureRehearsals($membership->user_id);
            $membership->delete();
        });
    }

    private function removeUserFromFutureRehearsals(int $memberId): void
    {
        DB::table('rehearsal_user')
            ->where('rehearsal_user.user_id', $memberId)
            ->join('rehearsals', function (JoinClause $join) {
                $join->on('rehearsals.id', '=', 'rehearsal_user.rehearsal_id')
                    ->whereRaw('time && ?', [
                        new TimestampRange(Carbon::now(), null),
                    ])
                    ->where('band_id', $this->id);
            })
            ->delete();
    }

    public function cancelInvites(): void
    {
        $this->invites()->delete();
    }

    /**
     * @return HasMany<Invite>
     */
    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class);
    }

    public function hasMember(int $memberId): bool
    {
        return $this->members->contains((string) $memberId);
    }
}

<?php

namespace App\Models;

use App\Http\Requests\Filters\FilterRequest;
use Belamov\PostgresRange\Ranges\TimestampRange;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
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
 * @property-read Collection|User[] $invitedUsers
 * @property-read int|null $invited_users_count
 * @property-read Collection|Rehearsal[] $futureRehearsals
 * @property-read int|null $future_rehearsals_count
 * @property Carbon|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Band onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|Band whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Band withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Band withoutTrashed()
 * @property-read Collection|BandGenre[] $genres
 * @property-read int|null $genres_count
 * @method static Builder|Band filter(FilterRequest $filters)
 * @property string|null $bio
 * @method static Builder|Band whereBio($value)
 */
class Band extends Model
{
    use SoftDeletes;
    use HasFactory;
    use Filterable;

    protected $guarded = [
        'id',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rehearsals(): HasMany
    {
        return $this->hasMany(Rehearsal::class);
    }

    public function futureRehearsals(): HasMany
    {
        return $this->hasMany(Rehearsal::class)->whereRaw('time && ?', [
            new TimestampRange(Carbon::now(), null),
        ]);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            BandGenre::class,
            'bands_genres',
            'band_id',
            'genre_id'
        );
    }

    public function invite(User | int $user): Invite
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Invite::create([
            'user_id' => $userId,
            'band_id' => $this->id,
        ]);
    }

    /**
     * @param  int  $userId
     * @param  string|null  $role
     * @throws Throwable
     */
    public function addMember(int $userId, ?string $role = null): void
    {
        DB::transaction(function () use ($role, $userId) {
            $this->members()->attach($userId, ['role' => $role]);
            $this->addUserToFutureRehearsals($userId);
        });
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    private function addUserToFutureRehearsals(int $userId): void
    {
        //TODO: optimize query
        $this->futureRehearsals->each(static function (Rehearsal $futureRehearsal) use ($userId) {
            $futureRehearsal->attendees()->attach($userId);
        });
    }

    /**
     * @param  int  $memberId
     * @throws Throwable
     */
    public function removeMember(int $memberId): void
    {
        DB::transaction(function () use ($memberId) {
            $this->removeUserFromFutureRehearsals($memberId);
            $this->members()->detach([$memberId]);
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
        $this->invitedUsers()->delete();
    }

    public function invitedUsers(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'band_user_invites')
            ->withPivot('role')
            ->withTimestamps()
            ->using(Invite::class);
    }

    public function hasMember(int $memberId): bool
    {
        return $this->members->contains($memberId);
    }
}

<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Band
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
 */
class Band extends Model
{
    protected $guarded = [
        'id'
    ];

    /**
     * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return HasMany
     */
    public function rehearsals(): HasMany
    {
        return $this->hasMany(Rehearsal::class);
    }

    /**
     * @return HasMany
     */
    public function futureRehearsals(): HasMany
    {
        return $this->hasMany(Rehearsal::class)->where('starts_at', '>', Carbon::now());
    }

    /**
     * @return BelongsToMany
     */
    public function invitedUsers(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'band_user_invites')
            ->withTimestamps()
            ->using(Invite::class);
    }

    /**
     * @param $user
     * @return Invite
     */
    public function invite($user): Invite
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Invite::create([
            'user_id' => $userId,
            'band_id' => $this->id
        ]);
    }

    /**
     * @param int $userId
     */
    public function addMember(int $userId): void
    {
        $this->members()->attach($userId);
        $this->addUserToFutureRehearsals($userId);
    }

    /**
     * @param int $memberId
     */
    public function removeMember(int $memberId): void
    {
        $this->removeUserFromFutureRehearsals($memberId);
        $this->members()->detach([$memberId]);
    }

    /**
     * @param int $userId
     */
    private function addUserToFutureRehearsals(int $userId): void
    {
        $this->futureRehearsals->each(static function (Rehearsal $futureRehearsal) use ($userId) {
            $futureRehearsal->attendees()->attach($userId);
        });
    }

    /**
     * @param int $memberId
     */
    private function removeUserFromFutureRehearsals(int $memberId): void
    {
        $this->futureRehearsals->each(static function (Rehearsal $futureRehearsal) use ($memberId) {
            $futureRehearsal->attendees()->detach($memberId);
        });
    }
}

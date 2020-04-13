<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Models\User.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|Organization[] $organizations
 * @property int $type
 * @property-read int|null $notifications_count
 * @property-read int|null $organizations_count
 * @method static Builder|User whereType($value)
 * @property-read Collection|Band[] $createdBands
 * @property-read int|null $createdBands_count
 * @property-read Collection|Band[] $bands
 * @property-read int|null $bands_count
 * @property-read int|null $created_bands_count
 * @property-read Collection|Rehearsal[] $rehearsals
 * @property-read int|null $rehearsals_count
 * @property-read Collection|Band[] $invites
 * @property-read int|null $invites_count
 */
class User extends Authenticatable
{
    public const TYPE_USER = 1;

    use Notifiable;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return HasMany
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    /**
     * Bands that user is admin of.
     *
     * @return HasMany
     */
    public function createdBands(): HasMany
    {
        return $this->hasMany(Band::class, 'admin_id');
    }

    /**
     * Bands that user is member of.
     *
     * @return BelongsToMany
     */
    public function bands(): BelongsToMany
    {
        return $this->belongsToMany(Band::class);
    }

    /**
     * @return BelongsToMany
     */
    public function rehearsals(): BelongsToMany
    {
        return $this->belongsToMany(Rehearsal::class);
    }

    /**
     * Returns bands that invited user to join.
     *
     * @return BelongsToMany
     */
    public function invites(): BelongsToMany
    {
        return $this
            ->belongsToMany(Band::class, 'band_user_invites')
            ->withTimestamps()
            ->using(Invite::class);
    }
}

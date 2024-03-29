<?php

namespace App\Models;

use App\Models\Organization\Organization;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\User.
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
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
 * @property-read Collection|Organization[] $favoriteOrganizations
 * @property-read int|null $favorite_organizations_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property string|null $phone
 * @property string|null $link
 * @method static Builder|User whereLink($value)
 * @method static Builder|User wherePhone($value)
 * @property string|null $avatar
 * @method static UserFactory factory(...$parameters)
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property string|null $password
 * @property string|null $remember_token
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 */
class User extends Authenticatable implements HasMedia
{
    use Notifiable;
    use HasFactory;
    use HasAvatar;

    protected $guarded = ['id'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return HasMany<Organization>
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<Organization>
     */
    public function favoriteOrganizations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'organizations_users_favorites',
            'user_id',
            'organization_id'
        );
    }

    /**
     * @return HasMany<Band>
     */
    public function createdBands(): HasMany
    {
        return $this->hasMany(Band::class, 'admin_id');
    }

    /**
     * @return HasManyThrough<Band>
     */
    public function bands(): HasManyThrough
    {
        return $this->hasManyThrough(
            Band::class,
            BandMembership::class,
            'user_id',
            'id',
            'id',
            'band_id'
        );
    }

    /**
     * @return BelongsToMany<Rehearsal>
     */
    public function rehearsals(): BelongsToMany
    {
        return $this->belongsToMany(Rehearsal::class);
    }

    /**
     * @return HasMany<Invite>
     */
    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class, 'email', 'email');
    }
}

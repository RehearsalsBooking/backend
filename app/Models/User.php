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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

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
 * @property-read Collection|Organization[] $favoriteOrganizations
 * @property-read int|null $favorite_organizations_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property string|null $public_email
 * @property string|null $phone
 * @property string|null $link
 * @method static Builder|User whereLink($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User wherePublicEmail($value)
 * @property string|null $avatar
 * @method static Builder|User whereAvatar($value)
 * @method static UserFactory factory(...$parameters)
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function favoriteOrganizations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'organizations_users_favorites',
            'user_id',
            'organization_id'
        );
    }

    public function createdBands(): HasMany
    {
        return $this->hasMany(Band::class, 'admin_id');
    }

    public function bands(): BelongsToMany
    {
        return $this->belongsToMany(Band::class);
    }

    public function rehearsals(): BelongsToMany
    {
        return $this->belongsToMany(Rehearsal::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class, 'email', 'email');
    }
}

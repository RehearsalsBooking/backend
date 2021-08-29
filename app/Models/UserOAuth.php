<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Throwable;

/**
 * App\Models\UserOAuth
 *
 * @property int $id
 * @property int $user_id
 * @property string $social_id
 * @property string $social_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @method static Builder|UserOAuth newModelQuery()
 * @method static Builder|UserOAuth newQuery()
 * @method static Builder|UserOAuth query()
 * @method static Builder|UserOAuth whereCreatedAt($value)
 * @method static Builder|UserOAuth whereId($value)
 * @method static Builder|UserOAuth whereSocialId($value)
 * @method static Builder|UserOAuth whereSocialType($value)
 * @method static Builder|UserOAuth whereUpdatedAt($value)
 * @method static Builder|UserOAuth whereUserId($value)
 * @mixin Eloquent
 */
class UserOAuth extends Model
{
    use HasFactory;

    protected $table = 'user_oauths';

    protected $guarded = [];

    /**
     * @throws Throwable
     */
    public static function fromSocialiteUser(SocialiteUser $socialiteUser, string $provider): User
    {
        $oAuthUser = self::where('social_id', $socialiteUser->getId())
            ->where('social_type', $provider)
            ->first();

        if ($oAuthUser) {
            return $oAuthUser->user;
        }

        return DB::transaction(function () use ($provider, $socialiteUser) {
            $user = User::create([
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
            ]);
            self::create([
                'social_id' => $socialiteUser->getId(),
                'social_type' => $provider,
                'user_id' => $user->id,
            ]);
            return $user;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
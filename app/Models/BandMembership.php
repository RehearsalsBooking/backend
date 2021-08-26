<?php

namespace App\Models;

use Database\Factories\BandMembershipFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\BandMembership
 *
 * @property int $id
 * @property int $band_id
 * @property int $user_id
 * @property string|null $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @method static BandMembershipFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership newQuery()
 * @method static Builder|BandMembership onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership query()
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership whereBandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BandMembership whereUserId($value)
 * @method static Builder|BandMembership withTrashed()
 * @method static Builder|BandMembership withoutTrashed()
 * @mixin Eloquent
 */
class BandMembership extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

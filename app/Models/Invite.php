<?php

namespace App\Models;

use DB;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * App\Models\Invite.
 *
 * @property int $id
 * @property int $band_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Invite newModelQuery()
 * @method static Builder|Invite newQuery()
 * @method static Builder|Invite query()
 * @method static Builder|Invite whereBandId($value)
 * @method static Builder|Invite whereCreatedAt($value)
 * @method static Builder|Invite whereId($value)
 * @method static Builder|Invite whereUpdatedAt($value)
 * @method static Builder|Invite whereUserId($value)
 * @mixin Eloquent
 * @property-read Band $band
 * @property string|null $role
 * @method static Builder|Invite whereRole($value)
 * @property-read User $user
 */
class Invite extends Pivot
{
    use HasFactory;

    public $incrementing = true;
    protected $table = 'band_user_invites';

    public function band(): BelongsTo
    {
        return $this->belongsTo(Band::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Adds invited user to band's members.
     *
     * @throws Exception
     * @throws Throwable
     */
    public function accept(): void
    {
        DB::transaction(function () {
            $this->band->addMember($this->user_id, $this->role);
            $this->delete();
        });
    }

    /**
     * @throws Exception
     */
    public function decline(): void
    {
        $this->delete();
    }
}

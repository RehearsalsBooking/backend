<?php

namespace App\Models;

use DB;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * App\Models\Invite.
 *
 * @property int $id
 * @property int $band_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Invite newModelQuery()
 * @method static Builder|Invite newQuery()
 * @method static Builder|Invite query()
 * @method static Builder|Invite whereBandId($value)
 * @method static Builder|Invite whereCreatedAt($value)
 * @method static Builder|Invite whereId($value)
 * @method static Builder|Invite whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Band $band
 * @property string|null $role
 * @method static Builder|Invite whereRole($value)
 * @property string $email
 * @property int $status
 * @method static Builder|Invite whereEmail($value)
 * @method static Builder|Invite whereStatus($value)
 */
class Invite extends Model
{
    use HasFactory;
    use Filterable;

    public const STATUS_PENDING = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_REJECTED = 3;

    protected $guarded = ['id'];

    public function band(): BelongsTo
    {
        return $this->belongsTo(Band::class);
    }

    /**
     * Adds invited user to band's members.
     *
     * @throws Exception
     * @throws Throwable
     */
    public function accept(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->band->addMember($user->id, $this->role);
            $this->update(['status' => self::STATUS_ACCEPTED]);
        });
    }

    /**
     * @throws Exception
     */
    public function decline(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }
}

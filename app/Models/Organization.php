<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organization
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $coordinates
 * @property bool $verified
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Organization newModelQuery()
 * @method static Builder|Organization newQuery()
 * @method static Builder|Organization query()
 * @method static Builder|Organization whereAddress($value)
 * @method static Builder|Organization whereCoordinates($value)
 * @method static Builder|Organization whereCreatedAt($value)
 * @method static Builder|Organization whereId($value)
 * @method static Builder|Organization whereName($value)
 * @method static Builder|Organization whereUpdatedAt($value)
 * @method static Builder|Organization whereVerified($value)
 * @mixin Eloquent
 * @method static Builder|Organization verified()
 */
class Organization extends Model
{
    public function scopeVerified(Builder $query)
    {
        return $query->where('verified', true);
    }
}

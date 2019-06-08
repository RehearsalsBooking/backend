<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\WorkingDay
 *
 * @method static Builder|WorkingDay newModelQuery()
 * @method static Builder|WorkingDay newQuery()
 * @method static Builder|WorkingDay query()
 * @mixin Eloquent
 * @property int $id
 * @property int $organization_id
 * @property int $day
 * @property string|null $opens_at
 * @property string|null $closes_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|WorkingDay whereClosesAt($value)
 * @method static Builder|WorkingDay whereCreatedAt($value)
 * @method static Builder|WorkingDay whereDay($value)
 * @method static Builder|WorkingDay whereId($value)
 * @method static Builder|WorkingDay whereOpensAt($value)
 * @method static Builder|WorkingDay whereOrganizationId($value)
 * @method static Builder|WorkingDay whereUpdatedAt($value)
 */
class WorkingDay extends Model
{
}

<?php

namespace App\Models;

use Belamov\PostgresRange\Casts\TimeRangeCast;
use Belamov\PostgresRange\Ranges\TimeRange;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Price
 *
 * @property int $id
 * @property int $day
 * @property float $price
 * @property int $organization_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization $organization
 * @method static Builder|OrganizationPrice newModelQuery()
 * @method static Builder|OrganizationPrice newQuery()
 * @method static Builder|OrganizationPrice query()
 * @method static Builder|OrganizationPrice whereCreatedAt($value)
 * @method static Builder|OrganizationPrice whereDay($value)
 * @method static Builder|OrganizationPrice whereId($value)
 * @method static Builder|OrganizationPrice whereOrganizationId($value)
 * @method static Builder|OrganizationPrice wherePrice($value)
 * @method static Builder|OrganizationPrice whereUpdatedAt($value)
 * @mixin Eloquent
 * @property string $starts_at
 * @property string $ends_at
 * @method static Builder|OrganizationPrice whereEndsAt($value)
 * @method static Builder|OrganizationPrice whereStartsAt($value)
 * @property TimeRange $time
 */
class OrganizationPrice extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'time' => TimeRangeCast::class
    ];
}

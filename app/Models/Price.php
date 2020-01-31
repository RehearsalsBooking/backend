<?php

namespace App\Models;

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
 * @method static Builder|Price newModelQuery()
 * @method static Builder|Price newQuery()
 * @method static Builder|Price query()
 * @method static Builder|Price whereCreatedAt($value)
 * @method static Builder|Price whereDay($value)
 * @method static Builder|Price whereId($value)
 * @method static Builder|Price whereOrganizationId($value)
 * @method static Builder|Price wherePrice($value)
 * @method static Builder|Price whereUpdatedAt($value)
 * @mixin Eloquent
 * @property string $starts_at
 * @property string $ends_at
 * @method static Builder|Price whereEndsAt($value)
 * @method static Builder|Price whereStartsAt($value)
 */
class Price extends Model
{
    protected $guarded = ['id'];
}

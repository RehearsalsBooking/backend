<?php

namespace App\Models\Organization;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organization\OrganizationEquipment
 *
 * @property int $id
 * @property string $item_description
 * @property string|null $model
 * @property string|null $photo
 * @property int $organization_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|OrganizationEquipment newModelQuery()
 * @method static Builder|OrganizationEquipment newQuery()
 * @method static Builder|OrganizationEquipment query()
 * @method static Builder|OrganizationEquipment whereCreatedAt($value)
 * @method static Builder|OrganizationEquipment whereId($value)
 * @method static Builder|OrganizationEquipment whereItemDescription($value)
 * @method static Builder|OrganizationEquipment whereModel($value)
 * @method static Builder|OrganizationEquipment whereOrganizationId($value)
 * @method static Builder|OrganizationEquipment wherePhoto($value)
 * @method static Builder|OrganizationEquipment whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrganizationEquipment extends Model
{
    protected $guarded = ['id'];
}

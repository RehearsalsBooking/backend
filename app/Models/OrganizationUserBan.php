<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * App\Models\OrganizationUserBan
 *
 * @property int $organization_id
 * @property int $user_id
 * @property string|null $comment
 * @property Carbon $created_at
 * @method static Builder|OrganizationUserBan newModelQuery()
 * @method static Builder|OrganizationUserBan newQuery()
 * @method static Builder|OrganizationUserBan query()
 * @method static Builder|OrganizationUserBan whereComment($value)
 * @method static Builder|OrganizationUserBan whereCreatedAt($value)
 * @method static Builder|OrganizationUserBan whereOrganizationId($value)
 * @method static Builder|OrganizationUserBan whereUserId($value)
 * @mixin Eloquent
 * @property Carbon|null $updated_at
 * @method static Builder|OrganizationUserBan whereUpdatedAt($value)
 */
class OrganizationUserBan extends Pivot
{
    protected $table = 'organizations_users_bans';
}

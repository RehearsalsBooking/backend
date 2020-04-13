<?php

namespace App\Models\Organization;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organization\OrganizationUserBan.
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
 * @property int $id
 * @method static Builder|OrganizationUserBan whereId($value)
 */
class OrganizationUserBan extends Pivot
{
    public $incrementing = true;
    protected $table = 'organizations_users_bans';

    /**
     * @param Organization $organization
     * @return bool
     */
    public function byOrganization(Organization $organization): bool
    {
        return $this->organization_id === $organization->id;
    }
}

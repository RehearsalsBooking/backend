<?php

namespace App\Policies\Management;

use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage rehearsals of organization.
     *
     * User can book rehearsal on behalf of a band only if he is
     * the admin of that band
     *
     * @param  User  $user
     * @param  Organization  $organization
     * @return bool
     */
    public function manage(User $user, Organization $organization): bool
    {
        return $user->organizations->contains($organization);
    }

    /**
     * @param  User  $user
     * @param  Organization  $organization
     * @return bool
     */
    public function see(?User $user, Organization $organization): bool
    {
        if ($user === null) {
            return true;
        }

        return ! $organization->isUserBanned($user->id);
    }
}

<?php

namespace App\Policies\Management;

use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, Organization $organization): bool
    {
        return $user->organizations->contains($organization);
    }

    public function see(?User $user, Organization $organization): bool
    {
        if ($user === null) {
            return true;
        }

        return ! $organization->isUserBanned($user->id);
    }
}

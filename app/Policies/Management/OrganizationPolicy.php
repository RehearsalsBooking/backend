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
        return $organization->owner_id === $user->id;
    }
}

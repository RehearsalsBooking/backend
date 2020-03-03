<?php

namespace App\Policies\Management;

use App\Models\Rehearsal;
use App\Models\User;

/**
 * Trait RehearsalPoliciesForManagers
 * using trait because you can apply only one policy class to model
 *
 * @package App\Policies\Management
 */
trait RehearsalPoliciesForManagers
{

    /**
     * @param User $user
     * @param Rehearsal $rehearsal
     * @return bool
     */
    public function manageRehearsal(User $user, Rehearsal $rehearsal): bool
    {
        return $user->organizations->contains($rehearsal->organization);
    }
}

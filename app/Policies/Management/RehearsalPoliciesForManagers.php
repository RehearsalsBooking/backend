<?php

namespace App\Policies\Management;

use App\Models\Rehearsal;
use App\Models\User;

/**
 * Trait RehearsalPoliciesForManagers
 * using trait because you can apply only one policy class to model
 *
 * TODO: replace with middleware that checks if user is owner
 * @package App\Policies\Management
 */
trait RehearsalPoliciesForManagers
{
    /**
     * @param User $user
     * @param Rehearsal $rehearsal
     * @return bool
     */
    public function managementUpdateStatus(User $user, Rehearsal $rehearsal): bool
    {
        return $this->userIsOwnerOfRehearsalOrganization($user, $rehearsal);
    }

    /**
     * @param User $user
     * @param Rehearsal $rehearsal
     * @return bool
     */
    public function managementDelete(User $user, Rehearsal $rehearsal): bool
    {
        return $this->userIsOwnerOfRehearsalOrganization($user, $rehearsal);
    }

    /**
     * @param User $user
     * @param Rehearsal $rehearsal
     * @return bool
     */
    protected function userIsOwnerOfRehearsalOrganization(User $user, Rehearsal $rehearsal): bool
    {
        return $user->organizations->contains($rehearsal->organization);
    }
}

<?php

namespace App\Policies\Users;

use App\Models\User;
use App\Models\Band;
use Illuminate\Auth\Access\HandlesAuthorization;

class BandPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the band.
     *
     * only admin of a band can update it
     * @param User $user
     * @param Band $band
     * @return bool
     */
    public function update(User $user, Band $band): bool
    {
        return $band->admin_id === $user->id;
    }

    /**
     * Determine whether the user can invite members to the band.
     *
     * only admin of a band can update its members
     * @param User $user
     * @param Band $band
     * @return bool
     */
    public function inviteMembers(User $user, Band $band): bool
    {
        return $band->admin_id === $user->id;
    }

    /**
     * Determine whether the user can remove members from the band.
     *
     * only admin of a band can update its members
     * @param User $user
     * @param Band $band
     * @return bool
     */
    public function removeMembers(User $user, Band $band): bool
    {
        return $band->admin_id === $user->id;
    }
}

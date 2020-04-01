<?php

namespace App\Policies\Users;

use App\Models\Band;
use App\Models\User;
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
     * Determine whether the user can delete the band.
     *
     * only admin of a band can update it
     * @param User $user
     * @param Band $band
     * @return bool
     */
    public function delete(User $user, Band $band): bool
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
     * @param int $memberId
     * @return bool
     */
    public function removeMember(User $user, Band $band, int $memberId): bool
    {
        // user can leave the band
        if ($memberId === $user->id) {
            return true;
        }

        if (!$band->hasMember($memberId)) {
            return false;
        }

        return $band->admin_id === $user->id;
    }
}

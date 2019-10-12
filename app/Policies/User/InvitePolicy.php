<?php

namespace App\Policies\User;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitePolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Invite $invite
     * @return bool
     */
    public function acceptInvite(User $user, Invite $invite): bool
    {
        return $user->id === $invite->user_id;
    }

    /**
     * @param User $user
     * @param Invite $invite
     * @return bool
     */
    public function declineInvite(User $user, Invite $invite): bool
    {
        return $user->id === $invite->user_id;
    }
}

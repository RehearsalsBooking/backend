<?php

namespace App\Policies\Users;

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
    public function accept(User $user, Invite $invite): bool
    {
        return $user->id === $invite->user_id;
    }

    /**
     * @param User $user
     * @param Invite $invite
     * @return bool
     */
    public function decline(User $user, Invite $invite): bool
    {
        return $user->id === $invite->user_id;
    }

    /**
     * @param User $user
     * @param Invite $invite
     * @return bool
     */
    public function cancel(User $user, Invite $invite): bool
    {
        return $user->id === $invite->band->admin_id;
    }
}

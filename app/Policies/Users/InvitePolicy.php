<?php

namespace App\Policies\Users;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitePolicy
{
    use HandlesAuthorization;

    public function accept(User $user, Invite $invite): bool
    {
        return $this->isInviteForUser($invite, $user);
    }

    public function decline(User $user, Invite $invite): bool
    {
        return $this->isInviteForUser($invite, $user);
    }

    public function cancel(User $user, Invite $invite): bool
    {
        return $user->id === $invite->band->admin_id;
    }

    protected function isInviteForUser(Invite $invite, User $user): bool
    {
        return $user->email === $invite->email;
    }
}

<?php

namespace App\Policies\Users;

use App\Models\User;
use App\Models\Rehearsal;
use Illuminate\Auth\Access\HandlesAuthorization;

class RehearsalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the rehearsal.
     *
     * @param User $user
     * @param Rehearsal $rehearsal
     * @return mixed
     */
    public function delete(User $user, Rehearsal $rehearsal)
    {
        return $user->id === $rehearsal->user_id;
    }
}

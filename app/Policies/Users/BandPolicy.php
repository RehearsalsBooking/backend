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
     * @param User $user
     * @param Band $band
     * @return mixed
     */
    public function update(User $user, Band $band)
    {
        return $band->admin_id === $user->id;
    }

}

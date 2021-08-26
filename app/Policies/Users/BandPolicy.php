<?php

namespace App\Policies\Users;

use App\Models\Band;
use App\Models\BandMembership;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BandPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, Band $band): bool
    {
        return $this->isUserIsBandAdmin($band, $user);
    }

    public function removeMember(User $user, Band $band, BandMembership $membership): bool
    {
        // band admin cannot leave band
        if ($membership->user_id === $band->admin_id) {
            return false;
        }

        // band should have membership
        if ($membership->band_id !== $band->id) {
            return false;
        }
        // user can leave the band
        if ($membership->user_id === $user->id) {
            return true;
        }

        return $this->isUserIsBandAdmin($band, $user);
    }

    /**
     * @param  Band  $band
     * @param  User  $user
     * @return bool
     */
    protected function isUserIsBandAdmin(Band $band, User $user): bool
    {
        return $band->admin_id === $user->id;
    }
}

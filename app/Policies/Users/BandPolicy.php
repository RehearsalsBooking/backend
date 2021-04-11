<?php

namespace App\Policies\Users;

use App\Models\Band;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BandPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, Band $band): bool
    {
        return $this->isUserIsBandAdmin($band, $user);
    }

    public function removeMember(User $user, Band $band, int $memberId): bool
    {
        // band admin cannot leave band
        if ($memberId === $band->admin_id) {
            return false;
        }

        // user can leave the band
        if ($memberId === $user->id) {
            return true;
        }

        //TODO: remove this validation logic from authorization
        if (! $band->hasMember($memberId)) {
            return false;
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

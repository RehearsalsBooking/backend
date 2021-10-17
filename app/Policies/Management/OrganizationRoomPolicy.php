<?php

namespace App\Policies\Management;

use App\Models\Organization\OrganizationRoom;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationRoomPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, OrganizationRoom $room): bool
    {
        return $room->organization->owner_id === $user->id;
    }
}

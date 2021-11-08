<?php

namespace App\Policies\Users;

use App\Models\Band;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RehearsalPolicy
{
    use HandlesAuthorization;

    public function create(User $user, ?int $bandId): bool
    {
        if ($bandId !== null) {
            $band = Band::findOrFail($bandId);

            return $band->admin_id === $user->id;
        }

        return true;
    }

    public function reschedule(User $user, Rehearsal $rehearsal): bool
    {
        if ($rehearsal->isIndividual()) {
            return $rehearsal->user_id === $user->id;
        }

        return optional($rehearsal->band)->admin_id === $user->id;
    }

    public function manage(User $user, Rehearsal $rehearsal): bool
    {
        return
            //user who booked
            $user->id === $rehearsal->user_id
            ||
            //admin of a band
            optional($rehearsal->band)->admin_id === $user->id
            ||
            // manager of organization where rehearsal was booked
            $rehearsal->organization->owner_id === $user->id;
    }

    public function seeFullInfo(User $user, Rehearsal $rehearsal): bool
    {
        return
            //user who booked
            $user->id === $rehearsal->user_id
            ||
            //any band member
            optional($rehearsal->band)->hasMember($user->id)
            ||
            // manager of organization where rehearsal was booked
            $rehearsal->organization->owner_id === $user->id;
    }
}

<?php

namespace App\Policies\Users;

use App\Models\Band;
use App\Models\Rehearsal;
use App\Models\User;
use App\Policies\Management\RehearsalPoliciesForManagers;
use Illuminate\Auth\Access\HandlesAuthorization;

class RehearsalPolicy
{
    use HandlesAuthorization;
    use RehearsalPoliciesForManagers;

    /**
     * Determine whether the user can book the rehearsal.
     *
     * User can book rehearsal on behalf of a band only if he is
     * the admin of that band
     *
     * @param User $user
     * @param Band $band
     * @return bool
     */
    public function createOnBehalfOfBand(User $user, Band $band): bool
    {
        return $band->admin_id === $user->id;
    }

    /**
     * Determine whether the user can reschedule the rehearsal.
     *
     * User can reschedule rehearsal on behalf of a band only if he is
     * the admin of that band
     *
     * @param User $user
     * @param Rehearsal $rehearsal
     * @return bool
     */
    public function reschedule(User $user, Rehearsal $rehearsal): bool
    {
        if ($rehearsal->isIndividual()) {
            return $rehearsal->user_id === $user->id;
        }

        return $rehearsal->band->admin_id === $user->id;
    }

    /**
     * Determine whether the user can delete the rehearsal.
     *
     * @param User $user
     * @param Rehearsal $rehearsal
     * @return mixed
     */
    public function delete(User $user, Rehearsal $rehearsal)
    {
        return
            //user can delete rehearsal that he booked
            $user->id === $rehearsal->user_id
            ||
            //admin of a band can delete rehearsal of band
            optional($rehearsal->band)->admin_id === $user->id;
    }
}

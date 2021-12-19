<?php

namespace App\Policies\Users;

use App\Models\Band;
use App\Models\Rehearsal;
use App\Models\User;
use DB;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RehearsalPolicy
{
    use HandlesAuthorization;

    public function create(User $user, int $roomId, ?int $bandId): Response
    {
        if ($this->userIsBannedInOrganization($user, $roomId)) {
            return Response::deny('Вы забанены в данной организации');
        }

        if (!$this->userCanBookOnBehalfOfBand($user, $bandId)) {
            return Response::deny('Вы не можете бронировать репетиции для данной группы');
        }

        return Response::allow();
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
            $rehearsal->room->organization->owner_id === $user->id;
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
            // manager of organization of room where rehearsal was booked
            $rehearsal->room->organization->owner_id === $user->id;
    }

    protected function userIsBannedInOrganization(User $user, int $roomId): bool
    {
        return DB::table('organizations_users_bans as bans')
            ->join('organization_rooms as rooms', 'rooms.organization_id', '=', 'bans.organization_id')
            ->where('bans.user_id', $user->id)
            ->where('rooms.id', $roomId)
            ->exists();
    }

    private function userCanBookOnBehalfOfBand(User $user, ?int $bandId): bool
    {
        if (!$bandId) {
            return true;
        }
        return Band::findOrFail($bandId, ['admin_id'])->admin_id === $user->id;
    }
}

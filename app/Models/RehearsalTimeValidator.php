<?php

namespace App\Models;

use App\Exceptions\User\TimeIsUnavailableForUsers;
use App\Exceptions\User\UserHasAnotherRehearsalAtThatTime;
use Belamov\PostgresRange\Ranges\TimeRange;
use DB;
use Illuminate\Database\Query\Builder;

class RehearsalTimeValidator
{

    /**
     * @throws TimeIsUnavailableForUsers
     * @throws UserHasAnotherRehearsalAtThatTime
     */
    public function validateThatSupposedAttendeesAreAvailable(
        string $startsAt,
        string $endsAt,
        ?int $userId = null,
        ?int $bandId = null,
        ?int $rehearsalId = null
    ): void {
        $supposedAttendees = $this->getSupposedRehearsalAttendees($userId, $bandId);
        $time = new TimeRange($startsAt, $endsAt);
        $rehearsalsForUsersAtTime = DB::table('rehearsals')
            ->join('rehearsal_user', 'rehearsal_user.rehearsal_id', '=', 'rehearsals.id')
            ->join('users', 'rehearsal_user.user_id', '=', 'users.id')
            ->whereIn('rehearsal_user.user_id', $supposedAttendees)
            ->where('time', '&&', $time)
            ->when($rehearsalId, fn(Builder $query) => $query->where('rehearsals.id', '!=', $rehearsalId))
            ->select(['users.name as user_name', 'users.id as user_id'])
            ->get();

        if ($rehearsalsForUsersAtTime->isEmpty()) {
            return;
        }
        if ($rehearsalsForUsersAtTime->count() === 1 && $rehearsalsForUsersAtTime->first()->user_id === $userId) {
            throw new UserHasAnotherRehearsalAtThatTime();
        }
        throw new TimeIsUnavailableForUsers($rehearsalsForUsersAtTime);
    }

    private function getSupposedRehearsalAttendees(?int $userId, ?int $bandId): array
    {
        return $bandId ? $this->getBandMembers($bandId) : [$userId];
    }

    private function getBandMembers(int $bandId): array
    {
        return DB::table('band_memberships')
            ->where('band_id', $bandId)
            ->select('user_id')
            ->pluck('user_id')
            ->toArray();
    }
}
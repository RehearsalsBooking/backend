<?php

namespace App\Models;

use App\Exceptions\User\InvalidRehearsalDurationException;
use App\Exceptions\User\TimeIsUnavailableForUsersException;
use App\Exceptions\User\TimeIsUnavailableInRoomException;
use App\Exceptions\User\TooLongRehearsalException;
use App\Exceptions\User\UserHasAnotherRehearsalAtThatTimeException;
use DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class RehearsalTimeValidator
{
    /**
     * @throws UserHasAnotherRehearsalAtThatTimeException
     * @throws TimeIsUnavailableForUsersException
     * @throws TimeIsUnavailableInRoomException
     * @throws InvalidRehearsalDurationException
     * @throws TooLongRehearsalException
     */
    public function validate(RehearsalDataProvider $rehearsal): void
    {
        $this->checkRehearsalDuration($rehearsal);
        $this->checkIfRoomIsAvailable($rehearsal);
        $this->checkIfSupposedAttendeesAreAvailable($rehearsal);
    }

    /**
     * @throws TimeIsUnavailableInRoomException
     */
    private function checkIfRoomIsAvailable(RehearsalDataProvider $rehearsal): void
    {
        $roomIsUnavailable = Rehearsal::query()
            ->where('organization_room_id', $rehearsal->roomId())
            ->where('time', '&&', $rehearsal->time())
            ->when($rehearsal->id(), fn(EloquentBuilder $query) => $query->where('id', '!=', $rehearsal->id()))
            ->exists();

        if ($roomIsUnavailable) {
            throw new TimeIsUnavailableInRoomException();
        }
    }

    /**
     * @throws TimeIsUnavailableForUsersException
     * @throws UserHasAnotherRehearsalAtThatTimeException
     */
    private function checkIfSupposedAttendeesAreAvailable(RehearsalDataProvider $rehearsal): void
    {
        $supposedAttendees = $this->getSupposedRehearsalAttendees($rehearsal->bookedUserId(), $rehearsal->bandId());
        $rehearsalsForUsersAtTime = DB::table('rehearsals')
            ->join('rehearsal_user', 'rehearsal_user.rehearsal_id', '=', 'rehearsals.id')
            ->join('users', 'rehearsal_user.user_id', '=', 'users.id')
            ->whereIn('rehearsal_user.user_id', $supposedAttendees)
            ->where('time', '&&', $rehearsal->time())
            ->when(
                $rehearsal->id() !== null,
                fn(QueryBuilder $query) => $query->where('rehearsals.id', '!=', $rehearsal->id())
            )
            ->select(['users.name as user_name', 'users.id as user_id'])
            ->get();

        if ($rehearsalsForUsersAtTime->isEmpty()) {
            return;
        }
        if ($rehearsalsForUsersAtTime->count() === 1 && $rehearsalsForUsersAtTime->first()->user_id === $rehearsal->bookedUserId()) {
            throw new UserHasAnotherRehearsalAtThatTimeException();
        }
        throw new TimeIsUnavailableForUsersException($rehearsalsForUsersAtTime);
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

    /**
     * @throws InvalidRehearsalDurationException
     * @throws TooLongRehearsalException
     */
    private function checkRehearsalDuration(RehearsalDataProvider $rehearsal): void
    {
        $rehearsalDuration = optional($rehearsal->time()->to())->diffInMinutes($rehearsal->time()->from());

        if ($rehearsalDuration % Rehearsal::MEASUREMENT_OF_REHEARSAL_DURATION_IN_MINUTES !== 0) {
            throw new InvalidRehearsalDurationException();
        }

        if ($rehearsalDuration > Rehearsal::MAXIMUM_REHEARSAL_DURATION_IN_MINUTES) {
            throw new TooLongRehearsalException();
        }
    }
}
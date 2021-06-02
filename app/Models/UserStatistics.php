<?php

namespace App\Models;

use DB;
use Illuminate\Support\Carbon;

class UserStatistics
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    //TODO: optimize - instead of 4 queries make only one (see issue #201)
    public function get(): array
    {
        return [
            'rehearsals_count' => $this->getRehearsalsCount(),
            'registered_at' => $this->getRegisteredAt(),
            'roles' => $this->getRoles(),
            'bands_count' => $this->getBandsCount(),
        ];
    }

    private function getRehearsalsCount(): int
    {
        return Rehearsal::completed()
            ->rightJoin('rehearsal_user', 'id', '=', 'rehearsal_id')
            ->where('rehearsal_user.user_id', $this->user->id)
            ->count();
    }

    private function getRegisteredAt(): ?Carbon
    {
        return $this->user->created_at;
    }

    private function getRoles(): array
    {
        return DB::table('band_user')
            ->select('role')
            ->distinct()
            ->where('user_id', $this->user->id)
            ->pluck('role')
            ->toArray();
    }

    private function getBandsCount(): int
    {
        return DB::table('band_user')
            ->where('user_id', $this->user->id)
            ->count();
    }


}
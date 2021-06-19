<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\RehearsalsFilterRequest;
use App\Http\Resources\RehearsalDetailedResource;
use App\Models\Rehearsal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserRehearsalsController extends Controller
{
    public function index(User $user, RehearsalsFilterRequest $filter): AnonymousResourceCollection
    {
        $rehearsals = Rehearsal::query()
            ->whereHas(
                'attendees',
                fn(Builder $query) => $query->where('id', $user->id)
            )
            ->filter($filter)
            ->with(['band', 'organization'])
            ->orderBy('time')
            ->get();

        return RehearsalDetailedResource::collection($rehearsals);
    }
}

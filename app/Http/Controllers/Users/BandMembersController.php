<?php

namespace App\Http\Controllers\Users;

use App\Http\Requests\Users\UpdateBandMembersRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\Band;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BandMembersController extends Controller
{
    //todo: replace with invitations
    public function update(UpdateBandMembersRequest $request, Band $band): AnonymousResourceCollection
    {
        $band->members()->sync($request->membersIds());

        return UserResource::collection($band->members);
    }
}

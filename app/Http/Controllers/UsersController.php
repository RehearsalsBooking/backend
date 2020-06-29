<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Users\OwnerResource;
use App\Models\User;

class UsersController extends Controller
{
    public function update(UpdateUserRequest $request)
    {
        $user = tap(
            auth()->user(),
            fn (User $user) => $user->update($request->getUserAttributes())
        );

        return response()->json(new OwnerResource($user->fresh()));
    }
}
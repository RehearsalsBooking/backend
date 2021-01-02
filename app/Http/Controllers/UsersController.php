<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    public function update(UpdateUserRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->getUserAttributes());

        return response()->json(new UserResource($user));
    }
}

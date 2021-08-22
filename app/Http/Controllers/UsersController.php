<?php

namespace App\Http\Controllers;

use App\Http\Requests\Management\UpdateAvatarRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UsersController extends Controller
{
    public function update(UpdateUserRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->getUserAttributes());

        return response()->json(new UserResource($user));
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function avatar(UpdateAvatarRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->updateAvatar($request->getAvatarFile());

        return response()->json($user->getAvatarUrls());
    }
}

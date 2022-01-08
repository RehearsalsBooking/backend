<?php

namespace App\Http\Controllers;

use App\Http\Requests\Management\UpdateAvatarRequest;
use App\Http\Requests\UpdateUserEmailRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\Users\LoggedUserResource;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class UsersController extends Controller
{
    public function update(UpdateUserRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->getUserAttributes());

        return response()->json(new LoggedUserResource($user));
    }

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function updateEmail(UpdateUserEmailRequest $request): JsonResponse
    {
        EmailVerification::validate($request->getEmailConfirmationCode());

        /** @var User $user */
        $user = auth()->user();

        DB::transaction(static function () use ($request, $user) {
            $user->update(['email' => $request->getNewEmail()]);
            EmailVerification::validated($request->getEmailConfirmationCode());
        });

        return response()->json(new LoggedUserResource($user));
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

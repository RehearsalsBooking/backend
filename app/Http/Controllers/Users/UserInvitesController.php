<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserInviteResource;
use App\Models\Invite;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class UserInvitesController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth()->user();

        $userInvites = $user->invites()->with('band')->get();

        return UserInviteResource::collection($userInvites);
    }

    /**
     * @param  Invite  $invite
     * @return JsonResponse
     * @throws Exception
     * @throws Throwable
     */
    public function accept(Invite $invite): JsonResponse
    {
        $this->authorize('accept', $invite);

        /** @var User $user */
        $user = auth()->user();

        $invite->accept($user);

        return response()->json('ok');
    }

    /**
     * @param  Invite  $invite
     * @return JsonResponse
     * @throws Exception
     */
    public function decline(Invite $invite): JsonResponse
    {
        $this->authorize('decline', $invite);

        $invite->decline();

        return response()->json('ok');
    }
}

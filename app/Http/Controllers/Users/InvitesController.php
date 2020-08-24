<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CreateBandInviteRequest;
use App\Models\Invite;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class InvitesController extends Controller
{
    /**
     * @param CreateBandInviteRequest $request
     * @return JsonResponse
     */
    public function create(CreateBandInviteRequest $request): JsonResponse
    {
        Invite::create($request->inviteParams());

        return response()->json('ok', Response::HTTP_CREATED);
    }

    /**
     * @param Invite $invite
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function delete(Invite $invite): JsonResponse
    {
        $this->authorize('cancel', $invite);

        $invite->delete();

        return response()->json();
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

        $invite->accept();

        return response()->json('ok');
    }

    /**
     * @param Invite $invite
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

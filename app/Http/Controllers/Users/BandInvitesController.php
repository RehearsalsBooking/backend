<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\AcceptBandInviteRequest;
use App\Http\Requests\Users\CreateBandInviteRequest;
use App\Models\Band;
use App\Models\Invite;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BandInvitesController extends Controller
{
    /**
     * @param CreateBandInviteRequest $request
     * @param Band $band
     * @return JsonResponse
     */
    public function create(CreateBandInviteRequest $request, Band $band): JsonResponse
    {
        $band->invite($request->invitedUser());

        return response()->json('ok', Response::HTTP_CREATED);
    }

    /**
     * @param Band $band
     * @param Invite $invite
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function delete(Band $band, Invite $invite): JsonResponse
    {
        $this->authorize('cancel-invites', $band);

        $invite->delete();

        return response()->json();
    }
}

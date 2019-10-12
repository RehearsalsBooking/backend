<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CreateBandInviteRequest;
use App\Http\Requests\Users\DeleteBandInviteRequest;
use App\Models\Band;
use App\Models\Invite;
use Exception;
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
     * @param DeleteBandInviteRequest $request
     * @param Band $band
     * @param Invite $invite
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(DeleteBandInviteRequest $request, Band $band, Invite $invite): JsonResponse
    {
        $invite->delete();

        return response()->json();
    }
}

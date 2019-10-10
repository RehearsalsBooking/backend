<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CreateBandInviteRequest;
use App\Http\Requests\Users\DeleteBandInviteRequest;
use App\Models\Band;
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
     * @return JsonResponse
     */
    public function delete(DeleteBandInviteRequest $request, Band $band): JsonResponse
    {
        $band->cancelInvite($request->invitedUser());

        return response()->json();
    }
}

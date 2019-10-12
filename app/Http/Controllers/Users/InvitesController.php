<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Invite;
use Exception;
use Illuminate\Http\JsonResponse;

class InvitesController extends Controller
{
    /**
     * @param Invite $invite
     * @return JsonResponse
     * @throws Exception
     */
    public function accept(Invite $invite): JsonResponse
    {
        $this->authorize('accept-invite', $invite);

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
        $this->authorize('decline-invite', $invite);

        $invite->decline();

        return response()->json('ok');
    }
}

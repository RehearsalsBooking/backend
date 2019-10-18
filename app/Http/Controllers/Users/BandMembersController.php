<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Band;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BandMembersController extends Controller
{
    /**
     * @param Band $band
     * @param int $memberId
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function delete(Band $band, int $memberId): JsonResponse
    {
        $this->authorize('remove-members', $band);

        $band->removeMember($memberId);

        return response()->json('band member successfully deleted', Response::HTTP_NO_CONTENT);
    }
}

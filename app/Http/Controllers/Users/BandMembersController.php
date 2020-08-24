<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Band;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class BandMembersController extends Controller
{
    /**
     * @param  Band  $band
     * @param  int  $memberId
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function delete(Band $band, int $memberId): JsonResponse
    {
        $this->authorize('remove-member', [$band, $memberId]);

        $band->removeMember($memberId);

        return response()->json('band member successfully deleted', Response::HTTP_NO_CONTENT);
    }
}

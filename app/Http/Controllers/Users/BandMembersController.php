<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateBandMemberRequest;
use App\Http\Resources\Users\BandDetailedResource;
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

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param  UpdateBandMemberRequest  $request
     * @param  Band  $band
     * @param  int  $memberId
     * @return BandDetailedResource
     * @throws AuthorizationException
     */
    public function update(UpdateBandMemberRequest $request, Band $band, int $memberId): BandDetailedResource
    {
        $this->authorize('manage', [$band]);

        $band->members()->updateExistingPivot($memberId, ['role' => $request->getNewRole()]);

        return new BandDetailedResource($band->fresh());
    }
}

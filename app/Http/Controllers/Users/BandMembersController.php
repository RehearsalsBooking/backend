<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateBandMemberRequest;
use App\Http\Resources\Users\BandMemberResource;
use App\Models\Band;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class BandMembersController extends Controller
{
    public function index(Band $band): AnonymousResourceCollection
    {
        $members = $band->members;

        return BandMemberResource::collection($members);
    }

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
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function update(UpdateBandMemberRequest $request, Band $band, int $memberId): AnonymousResourceCollection
    {
        $this->authorize('manage', [$band]);

        $band->members()->updateExistingPivot($memberId, ['role' => $request->getNewRole()]);

        return BandMemberResource::collection($band->members);
    }
}

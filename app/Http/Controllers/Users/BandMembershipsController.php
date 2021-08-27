<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateBandMemberRequest;
use App\Http\Resources\Users\BandMembershipResource;
use App\Models\Band;
use App\Models\BandMembership;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class BandMembershipsController extends Controller
{
    public function index(Band $band): AnonymousResourceCollection
    {
        $members = $band->memberships;

        return BandMembershipResource::collection($members);
    }

    /**
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function delete(Band $band, BandMembership $membership): JsonResponse
    {
        $this->authorize('remove-member', [$band, $membership]);

        $band->removeMembership($membership);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(
        UpdateBandMemberRequest $request,
        Band $band,
        BandMembership $membership
    ): AnonymousResourceCollection {
        $this->authorize('manage', [$band]);

        $membership->update(['roles' => $request->getNewRoles()]);

        return BandMembershipResource::collection($band->members);
    }
}

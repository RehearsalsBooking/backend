<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\BanUserRequest;
use App\Http\Resources\Management\OrganizationUserBanResource;
use App\Models\Organization;
use App\Models\OrganizationUserBan;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrganizationBansController extends Controller
{
    /**
     * @param BanUserRequest $request
     * @param Organization $organization
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function create(BanUserRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('manage', $organization);

        OrganizationUserBan::create($request->organizationUserBan());

        return response()->json('user successfully banned', Response::HTTP_CREATED);
    }

    /**
     * @param Organization $organization
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(Organization $organization): AnonymousResourceCollection
    {
        $this->authorize('manage', $organization);

        return OrganizationUserBanResource::collection($organization->bannedUsers);
    }

    /**
     * @param Organization $organization
     * @param OrganizationUserBan $ban
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function delete(Organization $organization, OrganizationUserBan $ban): JsonResponse
    {
        $this->authorize('manage', $organization);

        if (!$ban->byOrganization($organization)) {
            return response()->json(null, Response::HTTP_FORBIDDEN);
        }

        $ban->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

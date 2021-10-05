<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\OrganizationUpdateRequest;
use App\Http\Requests\Management\UpdateAvatarRequest;
use App\Http\Resources\Management\OrganizationResource;
use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class OrganizationsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth()->user();

        return OrganizationResource::collection(
            $user->organizations()->withoutGlobalScopes()->with('city')->get()
        );
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Organization $organization): OrganizationResource
    {
        $this->authorize('manage', $organization);

        return new OrganizationResource($organization);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(OrganizationUpdateRequest $request, Organization $organization): OrganizationResource
    {
        $this->authorize('manage', $organization);

        $organization->update($request->getAttributes());

        return new OrganizationResource($organization);
    }

    /**
     * @throws AuthorizationException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function avatar(
        UpdateAvatarRequest $request,
        Organization $organization
    ): JsonResponse {
        $this->authorize('manage', $organization);

        $organization->updateAvatar($request->getAvatarFile());

        return response()->json($organization->getAvatarUrls());
    }
}

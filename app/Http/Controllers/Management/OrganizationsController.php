<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\OrganizationUpdateRequest;
use App\Http\Requests\Management\UpdateOrganizationAvatarRequest;
use App\Http\Resources\Management\OrganizationResource;
use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class OrganizationsController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth()->user();

        return OrganizationResource::collection($user->organizations()->withoutGlobalScopes()->get());
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
     * @param  OrganizationUpdateRequest  $request
     * @param  Organization  $organization
     * @return OrganizationResource
     * @throws AuthorizationException
     */
    public function update(OrganizationUpdateRequest $request, Organization $organization): OrganizationResource
    {
        $this->authorize('manage', $organization);

        $attributes = $request->getAttributes();

        if ($request->has('avatar')) {
            $organization->deleteAvatar();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->file('avatar');
            $attributes['avatar'] = $uploadedFile->store('avatars', 'public');
        }

        $organization->update($attributes);

        return new OrganizationResource($organization);
    }

    /**
     * @throws AuthorizationException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function avatar(
        UpdateOrganizationAvatarRequest $request,
        Organization $organization
    ): JsonResponse {
        $this->authorize('manage', $organization);

        $file = $request->getAvatarFile();

        $organization->updateAvatar($file);

        return response()->json($organization->getAvatarUrls());
    }
}

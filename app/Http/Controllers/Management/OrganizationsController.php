<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\OrganizationUpdateRequest;
use App\Http\Resources\Management\OrganizationResource;
use App\Models\Organization\Organization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class OrganizationsController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return OrganizationResource::collection(auth()->user()->organizations()->withoutGlobalScopes()->get());
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
            Storage::disk('public')->delete($organization->avatar);
            $attributes['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $organization->update($attributes);


        return new OrganizationResource($organization);
    }
}

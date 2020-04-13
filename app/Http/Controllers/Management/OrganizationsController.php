<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\OrganizationUpdateRequest;
use App\Http\Resources\Management\OrganizationResource;
use App\Models\Organization\Organization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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

        $organization->update($request->getAttributes());

        return new OrganizationResource($organization);
    }
}

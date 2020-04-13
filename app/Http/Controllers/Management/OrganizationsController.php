<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\OrganizationResource;
use App\Http\Resources\Users\OrganizationDetailResource;
use App\Models\Organization;
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
     * @param  Organization  $organization
     * @return OrganizationDetailResource
     */
    public function show(Organization $organization): OrganizationDetailResource
    {
        return new OrganizationDetailResource($organization);
    }
}

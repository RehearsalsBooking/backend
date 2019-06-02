<?php

namespace App\Http\Controllers\Users;

use App\Http\Resources\Users\OrganizationResource;
use App\Models\Organization;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationsController extends Controller
{
    /**
     * Returns paginated list of all verified organizations
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return OrganizationResource::collection(Organization::verified()->paginate());
    }
}

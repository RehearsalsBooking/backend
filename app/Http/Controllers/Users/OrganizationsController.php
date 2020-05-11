<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\OrganizationsFilterRequest;
use App\Http\Resources\Users\OrganizationDetailResource;
use App\Http\Resources\Users\OrganizationResource;
use App\Models\Organization\Organization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationsController extends Controller
{
    /**
     * @param  OrganizationsFilterRequest  $request
     * @return AnonymousResourceCollection
     */
    public function index(OrganizationsFilterRequest $request): AnonymousResourceCollection
    {
        return OrganizationResource::collection(
            Organization::filter($request)
                ->when(
                    auth()->check(),
                    fn (Builder $query) => $query->withCount([
                        'favoritedUsers' => fn (Builder $query) => $query->where('user_id', auth()->id())
                    ])
                )
                ->get()
        );
    }

    /**
     * @param  Organization  $organization
     * @return OrganizationDetailResource
     * @throws AuthorizationException
     */
    public function show(Organization $organization): OrganizationDetailResource
    {
        $this->authorize('see', $organization);

        return new OrganizationDetailResource($organization);
    }
}

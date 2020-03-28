<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\CreateOrganizationPriceRequest;
use App\Http\Resources\OrganizationPriceResource;
use App\Models\Organization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrganizationPricesController extends Controller
{
    /**
     * @param Organization $organization
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(Organization $organization): AnonymousResourceCollection
    {
        $this->authorize('manage', $organization);
        return OrganizationPriceResource::collection($organization->prices);
    }

    /**
     * @param CreateOrganizationPriceRequest $request
     * @param Organization $organization
     * @return JsonResponse|AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function create(CreateOrganizationPriceRequest $request, Organization $organization)
    {
        $this->authorize('manage', $organization);

        if ($organization->hasPriceAt(
            $request->get('day'),
            $request->get('starts_at'),
            $request->get('ends_at')
        )) {
            return response()->json([
                'message' => 'this price entry intersects with other prices',
                'errors' => [
                    'day' => 'this price entry intersects with other prices',
                    'starts_at' => 'this price entry intersects with other prices',
                    'ends_at' => 'this price entry intersects with other prices'
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $organization->prices()->create($request->getAttributes());

        return (OrganizationPriceResource::collection($organization->prices)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED));
    }
}

<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\CreateOrganizationPriceRequest;
use App\Http\Requests\Management\UpdateOrganizationPriceRequest;
use App\Http\Resources\OrganizationPriceResource;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrganizationPricesController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function index(Organization $organization): AnonymousResourceCollection
    {
        $this->authorize('manage', $organization);

        return OrganizationPriceResource::collection($organization->prices);
    }

    /**
     * @throws AuthorizationException
     */
    public function create(
        CreateOrganizationPriceRequest $request,
        Organization $organization
    ): JsonResponse|AnonymousResourceCollection {
        $this->authorize('manage', $organization);

        if ($organization->hasPriceAt(
            $request->get('day'),
            $request->get('starts_at'),
            $request->get('ends_at')
        )) {
            $errorMessage = 'this price entry intersects with other prices';
            return response()->json([
                'message' => $errorMessage,
                'errors' => [
                    'day' => $errorMessage,
                    'starts_at' => $errorMessage,
                    'ends_at' => $errorMessage,
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $organization->prices()->create($request->getAttributes());

        return OrganizationPriceResource::collection($organization->prices)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(
        UpdateOrganizationPriceRequest $request,
        Organization $organization,
        OrganizationPrice $price
    ): OrganizationPriceResource {
        $this->authorize('manage', $organization);

        $price->update($request->getAttributes());

        return new OrganizationPriceResource($price);
    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function delete(Organization $organization, OrganizationPrice $price): JsonResponse
    {
        $this->authorize('manage', $organization);

        $price->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

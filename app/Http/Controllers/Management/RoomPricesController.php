<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\CreateRoomPriceRequest;
use App\Http\Requests\Management\UpdateRoomPriceRequest;
use App\Http\Resources\RoomPriceResource;
use App\Models\Organization\OrganizationRoom;
use App\Models\Organization\OrganizationRoomPrice;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoomPricesController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function index(OrganizationRoom $room): AnonymousResourceCollection
    {
        $this->authorize('manage', $room);

        return RoomPriceResource::collection($room->prices);
    }

    /**
     * @throws AuthorizationException
     */
    public function create(
        CreateRoomPriceRequest $request,
        OrganizationRoom $room
    ): JsonResponse|AnonymousResourceCollection {
        $this->authorize('manage', $room);

        if ($room->hasPriceAt(
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

        $room->prices()->create($request->getAttributes());

        return RoomPriceResource::collection($room->prices)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(
        UpdateRoomPriceRequest $request,
        OrganizationRoom $room,
        OrganizationRoomPrice $price
    ): RoomPriceResource {
        $this->authorize('manage', $room);

        $price->update($request->getAttributes());

        return new RoomPriceResource($price);
    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function delete(OrganizationRoom $room, OrganizationRoomPrice $price): JsonResponse
    {
        $this->authorize('manage', $room);

        $price->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

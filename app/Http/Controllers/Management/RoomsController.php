<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\CreateOrUpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationRoom;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RoomsController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function create(CreateOrUpdateRoomRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('manage', $organization);

        $room = $organization->rooms()->create($request->getAttributes());

        return (new RoomResource($room))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(
        CreateOrUpdateRoomRequest $request,
        Organization $organization,
        OrganizationRoom $room
    ): RoomResource {
        $this->authorize('manage', $organization);

        $room->update($request->getAttributes());

        return new RoomResource($room);
    }

    /**
     * @throws AuthorizationException
     */
    public function delete(Organization $organization, OrganizationRoom $room): JsonResponse
    {
        $this->authorize('manage', $organization);

        if ($room->futureRehearsals()->count() > 0) {
            throw new AuthorizationException('У данной комнаты есть репетиции в будущем');
        }
        $room->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}

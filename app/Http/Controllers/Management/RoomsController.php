<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\CreateRoomRequest;
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
    public function create(CreateRoomRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('manage', $organization);

        $room = $organization->rooms()->create($request->getAttributes());

        return (new RoomResource($room))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(CreateRoomRequest $request, Organization $organization, OrganizationRoom $room): RoomResource
    {
        $this->authorize('manage', $organization);

        $room->update($request->getAttributes());

        return new RoomResource($room);
    }

}

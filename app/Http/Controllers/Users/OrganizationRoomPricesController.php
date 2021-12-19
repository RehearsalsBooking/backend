<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomPriceResource;
use App\Models\Organization\OrganizationRoom;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationRoomPricesController extends Controller
{
    public function index(OrganizationRoom $room): AnonymousResourceCollection
    {
        return RoomPriceResource::collection($room->prices);
    }
}

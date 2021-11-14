<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Models\Organization\Organization;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomsController extends Controller
{
    public function index(Organization $organization): AnonymousResourceCollection
    {
        return RoomResource::collection($organization->rooms()->orderBy('id')->get());
    }
}

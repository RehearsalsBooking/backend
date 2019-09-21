<?php

namespace App\Http\Controllers\Users;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Organization;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RehearsalsController extends Controller
{
    /**
     * @param Organization $organization
     * @return AnonymousResourceCollection
     */
    public function index(Organization $organization): AnonymousResourceCollection
    {
        $rehearsals = $organization->rehearsals()->with(['user'])->get();

        return RehearsalResource::collection($rehearsals);
    }
}

<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class FavoriteOrganizationsController extends Controller
{
    /**
     * @param  Organization  $organization
     * @return JsonResponse
     */
    public function create(Organization $organization): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->favoriteOrganizations()->syncWithoutDetaching($organization);

        return response()->json(null, Response::HTTP_CREATED);
    }

    /**
     * @param  Organization  $organization
     * @return JsonResponse
     */
    public function delete(Organization $organization): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->favoriteOrganizations()->detach($organization);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

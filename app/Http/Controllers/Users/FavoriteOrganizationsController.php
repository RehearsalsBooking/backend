<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Organization\Organization;
use App\Models\User;
use Illuminate\Http\Response;

class FavoriteOrganizationsController extends Controller
{
    public function create(Organization $organization)
    {
        /** @var User $user */
        $user = auth()->user();

        $user->favoriteOrganizations()->syncWithoutDetaching($organization);

        return response()->json(null, Response::HTTP_CREATED);
    }
}

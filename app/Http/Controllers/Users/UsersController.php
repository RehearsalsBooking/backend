<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserResource;
use App\Models\User;

class UsersController extends Controller
{
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }
}

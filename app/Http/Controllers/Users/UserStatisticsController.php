<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStatistics;
use Illuminate\Http\JsonResponse;

class UserStatisticsController extends Controller
{
    public function index(User $user): JsonResponse
    {
        $statistics = new UserStatistics($user);
        return response()->json($statistics->get());
    }
}

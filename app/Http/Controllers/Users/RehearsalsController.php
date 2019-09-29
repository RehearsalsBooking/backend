<?php

namespace App\Http\Controllers\Users;

use App\Models\Rehearsal;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class RehearsalsController extends Controller
{
    /**
     * @param Rehearsal $rehearsal
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(Rehearsal $rehearsal): JsonResponse
    {
        $rehearsal->delete();

        return response()->json('rehearsal successfully deleted');
    }
}

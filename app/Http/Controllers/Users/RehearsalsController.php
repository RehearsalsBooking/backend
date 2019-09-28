<?php

namespace App\Http\Controllers\Users;

use App\Models\Rehearsal;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class RehearsalsController extends Controller
{
    /**
     * @param Rehearsal $rehearsal
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function delete(Rehearsal $rehearsal): JsonResponse
    {
        $this->authorize('delete', $rehearsal);

        $rehearsal->delete();

        return response()->json('rehearsal successfully deleted');
    }
}

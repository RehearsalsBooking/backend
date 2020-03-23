<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\RehearsalUpdateRequest;
use App\Http\Resources\Management\RehearsalDetailedResource;
use App\Models\Rehearsal;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class RehearsalsController
 *
 * Middlewares are used for authorization. See routes/management/api.php
 *
 * @package App\Http\Controllers\Management
 */
class RehearsalsController extends Controller
{
    /**
     * @param Rehearsal $rehearsal
     * @param RehearsalUpdateRequest $request
     * @return RehearsalDetailedResource
     */
    public function update(Rehearsal $rehearsal, RehearsalUpdateRequest $request): RehearsalDetailedResource
    {
        $rehearsal->update($request->getStatusAttribute());

        return new RehearsalDetailedResource($rehearsal);
    }

    /**
     * @param Rehearsal $rehearsal
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(Rehearsal $rehearsal): JsonResponse
    {
        $rehearsal->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

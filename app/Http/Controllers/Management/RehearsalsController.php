<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\RehearsalUpdateRequest;
use App\Http\Resources\Management\RehearsalDetailedResource;
use App\Models\Rehearsal;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
        $this->authorize('managementDelete', $rehearsal);

        $rehearsal->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

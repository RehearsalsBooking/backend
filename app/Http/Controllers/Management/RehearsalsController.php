<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\RehearsalsFilterManagementRequest;
use App\Http\Requests\Management\UpdateRehearsalRequest;
use App\Http\Resources\Management\RehearsalDetailedResource;
use App\Models\Rehearsal;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Class RehearsalsController.
 *
 * Middlewares are used for authorization. See routes/management/api.php
 */
class RehearsalsController extends Controller
{
    /**
     * @param RehearsalsFilterManagementRequest $filterRequest
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(RehearsalsFilterManagementRequest $filterRequest): AnonymousResourceCollection
    {
        $this->authorize('manage', $filterRequest->organization());

        return RehearsalDetailedResource::collection(Rehearsal::filter($filterRequest)->orderBy('id')->paginate());
    }

    /**
     * @param Rehearsal $rehearsal
     * @param UpdateRehearsalRequest $request
     * @return RehearsalDetailedResource
     */
    public function update(Rehearsal $rehearsal, UpdateRehearsalRequest $request): RehearsalDetailedResource
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

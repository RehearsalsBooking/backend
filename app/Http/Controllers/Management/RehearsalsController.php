<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\RehearsalsFilterManagementRequest;
use App\Http\Requests\Management\UpdateRehearsalRequest;
use App\Http\Resources\Management\RehearsalDetailedResource;
use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
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
     * @throws AuthorizationException
     */
    public function index(RehearsalsFilterManagementRequest $filterRequest): AnonymousResourceCollection
    {
        $this->authorize('manage', $filterRequest->organization());

        $rehearsals = Rehearsal::query()
            ->filter($filterRequest)
            ->orderBy('id')
            ->get();

        return RehearsalResource::collection($rehearsals);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Rehearsal $rehearsal, UpdateRehearsalRequest $request): RehearsalDetailedResource
    {
        $this->authorize('manage', $rehearsal);

        $rehearsal->update($request->getStatusAttribute());

        return new RehearsalDetailedResource($rehearsal);
    }

    /**
     * @throws AuthorizationException
     */
    public function delete(Rehearsal $rehearsal): JsonResponse
    {
        $this->authorize('manage', $rehearsal);

        $rehearsal->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

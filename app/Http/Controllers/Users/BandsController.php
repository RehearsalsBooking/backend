<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CreateBandRequest;
use App\Http\Requests\Users\UpdateBandRequest;
use App\Http\Resources\Users\BandResource;
use App\Models\Band;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BandsController extends Controller
{
    /**
     * @param CreateBandRequest $request
     * @return BandResource
     */
    public function create(CreateBandRequest $request): BandResource
    {
        $band = Band::create($request->getAttributes());

        $band->members()->attach(auth()->id());

        return new BandResource($band);
    }

    /**
     * @param UpdateBandRequest $request
     * @param Band $band
     * @return BandResource
     * @throws AuthorizationException
     */
    public function update(UpdateBandRequest $request, Band $band): BandResource
    {
        $this->authorize('update', $band);

        $band->update($request->getUpdatedBandAttributes());

        return new BandResource($band);
    }

    /**
     * @param Band $band
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function delete(Band $band): JsonResponse
    {
        $this->authorize('delete', $band);

        $band->cancelInvites();
        $band->futureRehearsals()->delete();
        $band->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

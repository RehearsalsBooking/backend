<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\BandsFilterRequest;
use App\Http\Requests\Users\CreateBandRequest;
use App\Http\Requests\Users\UpdateBandRequest;
use App\Http\Resources\Users\BandResource;
use App\Models\Band;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class BandsController extends Controller
{
    public function index(BandsFilterRequest $filter): AnonymousResourceCollection
    {
        $bands = Band::filter($filter)
            ->with('genres')
            ->withCount('members')
            ->get();

        return BandResource::collection($bands);
    }

    /**
     * @param  CreateBandRequest  $request
     * @return BandResource
     * @throws Throwable
     */
    public function create(CreateBandRequest $request): BandResource
    {
        return DB::transaction(static function () use ($request) {
            $band = Band::create($request->getAttributes());
            $band->members()->attach(auth()->id());

            return new BandResource($band);
        });
    }

    /**
     * @param  UpdateBandRequest  $request
     * @param  Band  $band
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
     * @param  Band  $band
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     * @throws Throwable
     */
    public function delete(Band $band): JsonResponse
    {
        $this->authorize('delete', $band);

        DB::transaction(static function () use ($band) {
            $band->cancelInvites();
            $band->futureRehearsals()->delete();
            $band->delete();
        });

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

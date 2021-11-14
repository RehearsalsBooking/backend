<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\BandsFilterRequest;
use App\Http\Requests\Management\UpdateAvatarRequest;
use App\Http\Requests\Users\CreateBandRequest;
use App\Http\Requests\Users\UpdateBandRequest;
use App\Http\Resources\Users\BandDetailedResource;
use App\Http\Resources\Users\BandResource;
use App\Models\Band;
use DB;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
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

    public function show(Band $band): BandDetailedResource
    {
        return new BandDetailedResource($band);
    }

    /**
     * @throws Throwable
     */
    public function create(CreateBandRequest $request): BandDetailedResource
    {
        return DB::transaction(static function () use ($request): BandDetailedResource {
            $band = Band::create($request->getAttributes());
            $band->genres()->sync($request->getBandGenres());

            return new BandDetailedResource($band);
        });
    }

    /**
     * @throws AuthorizationException
     */
    public function update(UpdateBandRequest $request, Band $band): BandDetailedResource
    {
        $this->authorize('manage', $band);

        $band->update($request->getUpdatedBandAttributes());
        $band->genres()->sync($request->getBandGenres());

        return new BandDetailedResource($band);
    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     * @throws Throwable
     */
    public function delete(Band $band): JsonResponse
    {
        $this->authorize('manage', $band);

        DB::transaction(static function () use ($band) {
            $band->cancelInvites();
            $band->futureRehearsals()->delete();
            $band->delete();
        });

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws AuthorizationException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function avatar(UpdateAvatarRequest $request, Band $band): JsonResponse
    {
        $this->authorize('manage', $band);

        $band->updateAvatar($request->getAvatarFile());

        return response()->json($band->getAvatarUrls());
    }
}

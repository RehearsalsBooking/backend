<?php

namespace App\Http\Controllers\Users;

use App\Http\Requests\Users\CreateBandRequest;
use App\Http\Requests\Users\UpdateBandRequest;
use App\Http\Resources\Users\BandResource;
use App\Models\Band;
use App\Http\Controllers\Controller;

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
     */
    public function update(UpdateBandRequest $request, Band $band): BandResource
    {
        $band->update($request->getUpdatedBandAttributes());

        return new BandResource($band);
    }
}

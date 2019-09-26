<?php

namespace App\Http\Controllers\Users;

use App\Http\Requests\Users\CreateBandRequest;
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

        return new BandResource($band);
    }
}

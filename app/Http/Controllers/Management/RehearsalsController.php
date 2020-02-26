<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\RehearsalUpdateRequest;
use App\Http\Resources\Management\RehearsalDetailedResource;
use App\Models\Rehearsal;

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
}

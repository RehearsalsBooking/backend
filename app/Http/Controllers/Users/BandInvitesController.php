<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\BandInviteResource;
use App\Models\Band;
use App\Models\Invite;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BandInvitesController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function index(Band $band): AnonymousResourceCollection
    {
        $this->authorize('manage', $band);

        $invites = Invite::query()->where('band_id', $band->id)->get();

        return BandInviteResource::collection($invites);
    }
}

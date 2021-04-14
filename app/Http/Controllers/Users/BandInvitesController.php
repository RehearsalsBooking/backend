<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\CreateBandInviteRequest;
use App\Http\Resources\Users\BandInviteResource;
use App\Mail\NewInvite;
use App\Models\Band;
use App\Models\Invite;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Mail;

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

    /**
     * @throws AuthorizationException
     */
    public function create(Band $band, CreateBandInviteRequest $request): JsonResponse
    {
        $this->authorize('manage', $band);

        $invite = $band->invites()->create($request->inviteParams());

        Mail::to($request->getInvitedEmail())->send(new NewInvite($band));

        return response()->json(new BandInviteResource($invite), Response::HTTP_CREATED);
    }

    /**
     * @param  Band  $band
     * @param  Invite  $invite
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function delete(Band $band, Invite $invite): JsonResponse
    {
        $this->authorize('manage', $band);

        $invite->delete();

        return response()->json();
    }
}

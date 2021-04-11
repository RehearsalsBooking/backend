<?php

namespace App\Http\Resources\Users;

use App\Models\Invite;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invite
 */
class BandInviteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->user),
            'role' => $this->role,
            'invited_at' => $this->created_at
        ];
    }
}

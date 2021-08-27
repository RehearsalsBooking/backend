<?php

namespace App\Http\Resources\Users;

use App\Models\Invite;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invite
 */
class UserInviteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'band' => new BandResource($this->band),
            'roles' => $this->roles,
            'invited_at' => $this->created_at,
        ];
    }
}

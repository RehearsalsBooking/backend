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
            'email' => $this->email,
            'roles' => $this->roles,
            'invited_at' => $this->created_at,
            'status' => $this->status,
        ];
    }
}

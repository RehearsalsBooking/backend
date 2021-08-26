<?php

namespace App\Http\Resources\Users;

use App\Models\BandMembership;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BandMembership
 */
class BandMembershipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->user),
            'joined_at' => $this->joined_at,
            'role' => $this->role,
        ];
    }
}

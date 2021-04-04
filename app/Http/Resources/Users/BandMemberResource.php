<?php

namespace App\Http\Resources\Users;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class BandMemberResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'joined_at' => $this->pivot->created_at,
            'role' => $this->pivot->role,
            'contacts' => [
                'public_email' => $this->public_email,
                'phone' => $this->phone,
                'link' => $this->link,
            ],
        ];
    }
}

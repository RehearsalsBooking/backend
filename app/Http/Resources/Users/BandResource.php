<?php

namespace App\Http\Resources\Users;

use App\Models\Band;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Band
 */
class BandResource extends JsonResource
{
    public function toArray($request): array
    {
        $userId = optional(auth())->id();
        $adminId = $this->admin_id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'members_count' => $this->members_count,
            'genres' => GenreResource::collection($this->genres),
            'is_admin' => $userId === $adminId,
        ];
    }
}

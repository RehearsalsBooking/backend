<?php

namespace App\Http\Resources;

use App\Models\Organization\OrganizationRoom;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrganizationRoom
 */
class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}

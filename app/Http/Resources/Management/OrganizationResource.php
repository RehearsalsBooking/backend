<?php

namespace App\Http\Resources\Management;

use App\Http\Resources\AvatarResource;
use App\Models\Organization\Organization;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Organization
 */
class OrganizationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gear' => $this->gear,
            'coordinates' => $this->coordinates,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'avatar' => $this->getAvatarUrls(),
        ];
    }
}

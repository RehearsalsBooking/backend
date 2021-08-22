<?php

namespace App\Http\Resources\Users;

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
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'avatar' => $this->getAvatarUrls(),
            'is_favorited' => $this->favorited_users_count > 0,
        ];
    }
}

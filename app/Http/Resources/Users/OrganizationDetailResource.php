<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AvatarResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\RoomPriceResource;
use App\Models\Organization\Organization;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Organization
 */
class OrganizationDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => new CityResource($this->city),
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'gear' => $this->gear,
            'avatar' => $this->getAvatarUrls(),
            'owner' => new UserResource($this->owner),
            'is_favorited' => $this->isUserFavorited((int) auth()->id()),
        ];
    }
}

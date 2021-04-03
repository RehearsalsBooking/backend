<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\OrganizationPriceResource;
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
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'gear' => $this->gear,
            'avatar' => $this->avatar,
            'owner' => new UserResource($this->owner),
            'prices' => OrganizationPriceResource::collection($this->prices),
            'is_favorited' => $this->isUserFavorited((int) auth()->id()),
        ];
    }
}

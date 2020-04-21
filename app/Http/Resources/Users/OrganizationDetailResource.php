<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\OrganizationPriceResource;
use App\Models\Organization\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrganizationDetailResource.
 *
 * @mixin Organization
 */
class OrganizationDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'description' => $this->description,
            'avatar' => $this->avatar,
            'owner' => new UserResource($this->owner),
            'prices' => OrganizationPriceResource::collection($this->prices),
        ];
    }
}

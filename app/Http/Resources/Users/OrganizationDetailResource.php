<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\OrganizationPriceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrganizationDetailResource.
 *
 * @mixin \App\Models\Organization\Organization
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
            'owner' => new UserResource($this->owner),
            'prices' => OrganizationPriceResource::collection($this->prices),
        ];
    }
}

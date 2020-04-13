<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\OrganizationPriceResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrganizationDetailResource.
 * @mixin Organization
 */
class OrganizationDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
            'opens_at' => $this->opens_at ?? null,
            'closes_at' => $this->closes_at ?? null,
            'owner' => new UserResource($this->owner),
            'prices' => OrganizationPriceResource::collection($this->prices),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Organization\OrganizationPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrganizationPriceResource.
 *
 * @mixin OrganizationPrice
 */
class OrganizationPriceResource extends JsonResource
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
            'day' => $this->day,
            'price' => $this->price,
            'starts_at' => $this->time->from(),
            'ends_at' => $this->time->to(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Organization\OrganizationPrice;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrganizationPrice
 */
class OrganizationPriceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'day' => $this->day,
            'price' => $this->price,
            'starts_at' => $this->time->from(),
            'ends_at' => $this->time->to(),
        ];
    }
}

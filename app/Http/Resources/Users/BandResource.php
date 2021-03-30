<?php

namespace App\Http\Resources\Users;

use App\Models\Band;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BandResource.
 * @mixin Band
 */
class BandResource extends JsonResource
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
            'members_count' => $this->members_count,
        ];
    }
}

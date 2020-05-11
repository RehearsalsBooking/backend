<?php

namespace App\Http\Resources\Users;

use App\Models\Band;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BandResource.
 *
 * @mixin Band
 */
class BandForUserResource extends JsonResource
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
            'is_admin' => $this->admin_id === auth()->id(),
        ];
    }
}

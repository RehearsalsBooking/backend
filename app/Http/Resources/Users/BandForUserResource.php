<?php

namespace App\Http\Resources\Users;

use App\Models\Band;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Band
 */
class BandForUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_admin' => $this->admin_id === auth()->id(),
        ];
    }
}

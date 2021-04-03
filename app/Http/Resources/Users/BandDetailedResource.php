<?php

namespace App\Http\Resources\Users;

use App\Models\Band;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Band
 */
class BandDetailedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio'=>$this->bio,
            'members' => UserResource::collection($this->members),
            'genres' => BandGenreResource::collection($this->genres),
        ];
    }
}

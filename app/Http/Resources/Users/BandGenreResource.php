<?php

namespace App\Http\Resources\Users;

use App\Models\BandGenre;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BandGenre
 */
class BandGenreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}

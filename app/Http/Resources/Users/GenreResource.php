<?php

namespace App\Http\Resources\Users;

use App\Models\Genre;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Genre
 */
class GenreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}

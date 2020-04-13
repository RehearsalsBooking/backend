<?php

namespace App\Http\Resources\Users;

use App\Models\Rehearsal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RehearsalResource.
 * @mixin Rehearsal
 */
class RehearsalResource extends JsonResource
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
            'starts_at' => $this->time->from()->toDateTimeString(),
            'ends_at' => $this->time->to()->toDateTimeString(),
        ];
    }
}

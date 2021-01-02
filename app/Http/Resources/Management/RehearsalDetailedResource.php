<?php

namespace App\Http\Resources\Management;

use App\Http\Resources\Users\BandResource;
use App\Http\Resources\Users\UserResource;
use App\Models\Rehearsal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RehearsalDetailedResource.
 * @mixin Rehearsal
 */
class RehearsalDetailedResource extends JsonResource
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
            'starts_at' => $this->time->from()?->toDateTimeString(),
            'ends_at' => $this->time->to()?->toDateTimeString(),
            'user' => new UserResource($this->user),
            'band' => new BandResource($this->band),
            'price' => $this->price,
            'is_confirmed' => $this->is_confirmed,
        ];
    }
}

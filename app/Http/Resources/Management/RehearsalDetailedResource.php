<?php

namespace App\Http\Resources\Management;

use App\Http\Resources\Users\BandResource;
use App\Http\Resources\Users\UserResource;
use App\Models\Rehearsal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RehearsalDetailedResource
 * @package App\Http\Resources\Management
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
            'starts_at' => $this->starts_at->toDateTimeString(),
            'ends_at' => $this->ends_at->toDateTimeString(),
            'user' => new UserResource($this->user),
            'band_id' => new BandResource($this->band),
            'price' => $this->price,
            'is_confirmed' => $this->is_confirmed
        ];
    }
}

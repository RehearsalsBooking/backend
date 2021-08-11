<?php

namespace App\Http\Resources;

use App\Http\Resources\Users\BandResource;
use App\Http\Resources\Users\OrganizationResource;
use App\Http\Resources\Users\UserResource;
use App\Models\Rehearsal;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Rehearsal
 */
class RehearsalDetailedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'starts_at' => optional($this->time->from())->toDateTimeString(),
            'ends_at' => optional($this->time->to())->toDateTimeString(),
            'is_individual' => $this->isIndividual(),
            'user' => new UserResource($this->user),
            'band' => new BandResource($this->band),
            'organization' => new OrganizationResource($this->organization),
            'price' => $this->price,
            'is_paid' => $this->is_paid,
        ];
    }
}

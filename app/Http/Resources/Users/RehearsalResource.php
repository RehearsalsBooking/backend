<?php

namespace App\Http\Resources\Users;

use App\Models\Rehearsal;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Rehearsal
 */
class RehearsalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'starts_at' => optional($this->time->from())->toDateTimeString(),
            'ends_at' => optional($this->time->to())->toDateTimeString(),
            'is_participant' => $this->is_participant ?? false,
        ];
    }
}

<?php

namespace App\Http\Resources\Management;

use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrganizationUserBanResource.
 * @mixin User
 */
class OrganizationUserBanResource extends JsonResource
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
            'user' => new UserResource($this),
            'comment' => $this->pivot->comment,
            'created_at' => $this->pivot->created_at,
        ];
    }
}

<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Management\OrganizationResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'contacts' => [
                'public_email' => $this->public_email,
                'phone' => $this->phone,
                'link' => $this->link,
            ],
            'organizations' => OrganizationResource::collection($this->getManagedOrganizations()),
        ];
    }
}

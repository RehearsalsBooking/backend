<?php

namespace App\Http\Resources\Users;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Management\OrganizationResource;

/**
 * @mixin User
 */
class LoggedUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->getAvatarUrls(),
            'contacts' => [
                'phone' => $this->phone,
                'link' => $this->link,
            ],
            'bands' => BandResource::collection($this->bands),
            'organizations' => OrganizationResource::collection($this->organizations)
        ];
    }
}

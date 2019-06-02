<?php

namespace App\Http\Resources\Users;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrganizationResource
 * @package App\Http\Resources\Users
 * @mixin Organization
 */
class OrganizationResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
        ];
    }
}

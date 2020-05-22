<?php

namespace App\Http\Resources\Users;

use App\Models\Organization\OrganizationEquipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrganizationDetailResource.
 *
 * @mixin OrganizationEquipment
 */
class OrganizationEquipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'item_description' => $this->item_description,
            'model' => $this->model,
            'photo' => $this->photo
        ];
    }
}

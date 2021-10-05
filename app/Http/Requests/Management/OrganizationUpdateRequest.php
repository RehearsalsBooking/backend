<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'address' => 'required',
            'city_id' => 'required|int|exists:cities,id'
        ];
    }

    public function getAttributes(): array
    {
        return [
            'name' => $this->get('name'),
            'address' => $this->get('address'),
            'coordinates' => $this->get('coordinates'),
            'gear' => $this->get('gear'),
            'city_id' => $this->get('city_id'),
        ];
    }
}

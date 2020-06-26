<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'address' => 'required',
            'avatar' => 'nullable|file',
        ];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'name' => $this->get('name'),
            'address' => $this->get('address'),
            'coordinates' => $this->get('coordinates'),
            'gear' => $this->get('gear'),
        ];
    }
}

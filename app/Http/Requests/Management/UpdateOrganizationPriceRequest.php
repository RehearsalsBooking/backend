<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationPriceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => 'required|numeric|min:0',
        ];
    }

    public function getAttributes(): array
    {
        return [
            'price' => $this->get('price'),
        ];
    }
}

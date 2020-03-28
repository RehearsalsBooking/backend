<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationPriceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'day' => 'required|numeric|between:1,7',
            'price' => 'required|numeric|min:0',
            'starts_at' => [
                'bail',
                'required',
                'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'
            ],
            'ends_at' => [
                'bail',
                'required',
                'regex:/^([0-1]?[0-9]|2[0-4]):[0-5][0-9]$/',
                function ($attribute, $value, $fail) {
                    if ($value < $this->get('starts_at')) {
                        $fail($attribute . ' must be after starts_at');
                    }
                },
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'day' => $this->get('day'),
            'price' => $this->get('price'),
            'starts_at' => $this->get('starts_at'),
            'ends_at' => $this->get('ends_at'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'public_email' => 'sometimes|email'
        ];
    }

    public function getUserAttributes(): array
    {
        return [
            'name' => $this->get('name'),
            'public_email' => $this->get('public_email'),
            'phone' => $this->get('phone'),
            'link' => $this->get('link'),
        ];
    }
}

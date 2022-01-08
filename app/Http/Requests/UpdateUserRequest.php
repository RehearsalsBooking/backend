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
        ];
    }

    public function getUserAttributes(): array
    {
        return [
            'name' => $this->get('name'),
            'link' => $this->get('link'),
        ];
    }
}

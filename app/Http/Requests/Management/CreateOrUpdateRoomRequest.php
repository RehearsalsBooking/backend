<?php

namespace App\Http\Requests\Management;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrUpdateRoomRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }

    public function getAttributes(): array
    {
        return [
            'name' => $this->get('name'),
        ];
    }
}
